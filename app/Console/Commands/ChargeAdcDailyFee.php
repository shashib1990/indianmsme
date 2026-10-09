<?php

namespace App\Console\Commands;

use App\Models\AdcProfile;
use App\Models\AdcWallet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargeAdcDailyFee extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'adc:charge-daily-fee {--dry-run : Run without making actual changes}';

    /**
     * The console command description.
     */
    protected $description = 'Deduct daily platform fee from ADC wallet. Only applies to ADCs who have made at least one sale.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $today = Carbon::today()->toDateString();

        if ($isDryRun) {
            $this->info('[DRY RUN] No actual changes will be made.');
        }

        // Only ADCs who have made at least one sale (first_sale_at is NOT NULL)
        // and are currently active
        $profiles = AdcProfile::whereNotNull('first_sale_at')
            ->with('user')
            ->get();

        $processed = 0;
        $deactivated = 0;
        $lowBalance = 0;

        foreach ($profiles as $profile) {
            $user = $profile->user;
            if (!$user) {
                continue;
            }

            $dailyCharge = (float) ($profile->per_user_daily_charge ?? 10.00);
            $currentBalance = (float) ($profile->wallet_balance ?? 0.00);

            // Skip if already deactivated due to wallet (no point charging again)
            if ($profile->deactivation_reason && str_contains($profile->deactivation_reason, 'Wallet balance')) {
                // Still deactivated, skip
                continue;
            }

            $newBalance = $currentBalance - $dailyCharge;

            if ($isDryRun) {
                $this->line("ADC #{$user->id} ({$user->name}): Balance ₹{$currentBalance} → ₹" . max(0, $newBalance));
                if ($newBalance <= 0) {
                    $this->warn("  → Would be DEACTIVATED (wallet exhausted)");
                } elseif ($newBalance < 50) {
                    $this->warn("  → LOW BALANCE warning");
                }
                $processed++;
                continue;
            }

            DB::beginTransaction();
            try {
                if ($newBalance <= 0) {
                    // Wallet exhausted — deactivate ADC
                    $profile->update([
                        'wallet_balance'      => 0.00,
                        'deactivation_reason' => 'Wallet balance exhausted. Daily platform fee of ₹' . number_format($dailyCharge, 2) . ' could not be fully deducted on ' . $today . '. Please recharge your wallet to resume sales.',
                    ]);

                    // Deactivate in users table
                    User::where('id', $user->id)->update(['is_active' => '0']);

                    // Record partial deduction if any balance was left
                    if ($currentBalance > 0) {
                        AdcWallet::create([
                            'adc_id'      => $user->id,
                            'amount'      => -$currentBalance,
                            'type'        => 'debit',
                            'description' => "Daily platform fee (partial) — ₹{$currentBalance} deducted on {$today}",
                        ]);
                    }

                    $deactivated++;
                    Log::info("ADC #{$user->id} ({$user->name}) deactivated due to wallet exhaustion.");
                } else {
                    // Normal deduction
                    $profile->update(['wallet_balance' => round($newBalance, 2)]);

                    AdcWallet::create([
                        'adc_id'      => $user->id,
                        'amount'      => -$dailyCharge,
                        'type'        => 'debit',
                        'description' => "Daily platform fee — ₹{$dailyCharge} deducted on {$today}",
                    ]);

                    if ($newBalance < 50) {
                        $lowBalance++;
                    }
                }

                DB::commit();
                $processed++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("ADC daily charge failed for ADC #{$user->id}: " . $e->getMessage());
                $this->error("Failed for ADC #{$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Daily charge complete. Processed: {$processed}, Deactivated: {$deactivated}, Low Balance: {$lowBalance}");
        return 0;
    }
}
