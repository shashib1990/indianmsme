<?php

namespace App\Console\Commands;

use App\Models\DdcPosOrder;
use App\Models\DdcProfile;
use App\Models\DdcWallet;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargeDdcDailyFee extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ddc:charge-daily-fee {--date= : The date to calculate fees for (YYYY-MM-DD), default yesterday} {--dry-run : Run without making actual changes}';

    /**
     * The console command description.
     */
    protected $description = "Deduct previous day's daily platform fee (1% of POS sales, max ₹100) from DDC wallet.";

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $targetDate = $this->option('date') ? Carbon::parse($this->option('date'))->toDateString() : Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        $this->info("Starting DDC daily platform fee processing for sales date: {$targetDate} (charging on: {$today})");
        if ($isDryRun) {
            $this->info('[DRY RUN] No actual changes will be made.');
        }

        // Fetch all DDC users. Rule 3: No first-sale exemption or validation in DDC.
        // Applies to all DDCs who made POS sales on the previous day.
        $ddcUsers = User::where('role', 'ddc')
            ->with('ddcProfile')
            ->get();

        $processed = 0;
        $deactivated = 0;
        $lowBalance = 0;
        $skippedZeroSales = 0;
        $skippedAlreadyCharged = 0;

        foreach ($ddcUsers as $user) {
            $profile = $user->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $user->id]);

            // Calculate total POS sales made by this DDC on target date
            $previousDaySales = (float) DdcPosOrder::where('ddc_id', $user->id)
                ->whereDate('created_at', $targetDate)
                ->sum('total_amount');

            if ($previousDaySales <= 0) {
                $skippedZeroSales++;
                continue;
            }

            // Check if fee was already charged for this specific sales date
            $alreadyCharged = DdcWallet::where('ddc_id', $user->id)
                ->where('type', 'debit')
                ->where('description', 'like', "%sales on {$targetDate}%")
                ->where('description', 'like', "%Daily platform fee%")
                ->exists();

            if ($alreadyCharged) {
                $this->line("DDC #{$user->id} ({$user->name}): Already charged platform fee for {$targetDate}, skipping.");
                $skippedAlreadyCharged++;
                continue;
            }

            // Calculate 1% fee with max cap of ₹100 (or custom per_user_daily_charge if set)
            $maxCharge = (float) ($profile->per_user_daily_charge ?? 100.00);
            if ($maxCharge <= 0) {
                $maxCharge = 100.00;
            }
            $calculatedFee = round($previousDaySales * 0.01, 2);
            $dailyCharge = min($maxCharge, $calculatedFee);

            if ($dailyCharge <= 0) {
                continue;
            }

            $currentBalance = (float) ($profile->wallet_balance ?? 0.00);

            // Skip if already deactivated due to zero balance and no balance available
            if ($currentBalance <= 0 && $profile->deactivation_reason && str_contains($profile->deactivation_reason, 'Wallet balance')) {
                $this->line("DDC #{$user->id} ({$user->name}): Wallet already exhausted at ₹0.00, skipping.");
                continue;
            }

            $newBalance = $currentBalance - $dailyCharge;

            if ($isDryRun) {
                $this->line("DDC #{$user->id} ({$user->name}): Sales ₹{$previousDaySales} → Fee ₹{$dailyCharge} | Balance ₹{$currentBalance} → ₹" . max(0, $newBalance));
                if ($newBalance <= 0) {
                    $this->warn("  → Would be DEACTIVATED (wallet exhausted)");
                } elseif ($newBalance <= 100) {
                    $this->warn("  → LOW BALANCE warning");
                }
                $processed++;
                continue;
            }

            DB::beginTransaction();
            try {
                if ($newBalance <= 0) {
                    // Wallet exhausted — deactivate DDC POS sale
                    $deactivationMsg = "Wallet balance exhausted. Daily platform fee of ₹" . number_format($dailyCharge, 2) . " (1% of ₹" . number_format($previousDaySales, 2) . " POS sales on {$targetDate}) could not be fully deducted on {$today}. Please recharge your wallet to resume POS sales.";

                    $profile->update([
                        'wallet_balance' => 0.00,
                        'deactivation_reason' => $deactivationMsg,
                    ]);

                    // Record partial deduction if any balance remained
                    if ($currentBalance > 0) {
                        DdcWallet::create([
                            'ddc_id' => $user->id,
                            'amount' => $currentBalance,
                            'type' => 'debit',
                            'description' => "Daily platform fee (partial) — ₹" . number_format($currentBalance, 2) . " deducted on {$today} (1% of ₹" . number_format($previousDaySales, 2) . " POS sales on {$targetDate})",
                        ]);
                    }

                    // Send notification to DDC
                    NotificationService::notifyDdc(
                        $user->id,
                        'POS Sales Deactivated - Low Wallet Balance',
                        "Your wallet balance reached ₹0.00 after daily platform fee deduction. POS sales have been temporarily deactivated. Please recharge your wallet to resume sales.",
                        route('ddc.wallet.index')
                    );

                    $deactivated++;
                } else {
                    // Normal deduction
                    $profile->update(['wallet_balance' => round($newBalance, 2)]);

                    DdcWallet::create([
                        'ddc_id' => $user->id,
                        'amount' => $dailyCharge,
                        'type' => 'debit',
                        'description' => "Daily platform fee — ₹" . number_format($dailyCharge, 2) . " deducted on {$today} (1% of ₹" . number_format($previousDaySales, 2) . " POS sales on {$targetDate}, max ₹100.00)",
                    ]);

                    NotificationService::notifyDdc(
                        $user->id,
                        'Daily Platform Fee Deducted',
                        "Daily platform fee of ₹" . number_format($dailyCharge, 2) . " has been deducted for POS sales on {$targetDate}. Current wallet balance: ₹" . number_format($newBalance, 2),
                        route('ddc.wallet.index')
                    );

                    if ($newBalance <= 100) {
                        NotificationService::notifyDdc(
                            $user->id,
                            'Low Wallet Balance Alert',
                            "Your wallet balance is low (₹" . number_format($newBalance, 2) . "). Daily platform fee of 1% of previous day POS sales will be deducted at midnight. Please recharge your wallet to avoid POS sales deactivation.",
                            route('ddc.wallet.index')
                        );
                        $lowBalance++;
                    }
                }

                DB::commit();
                $processed++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Failed to charge daily fee for DDC #{$user->id}: " . $e->getMessage(), ['exception' => $e]);
                $this->error("Failed for DDC #{$user->id}: " . $e->getMessage());
            }
        }

        $this->info("DDC daily charge complete. Processed: {$processed}, Deactivated: {$deactivated}, Low Balance: {$lowBalance}, Zero Sales: {$skippedZeroSales}, Already Charged: {$skippedAlreadyCharged}");
        return 0;
    }
}
