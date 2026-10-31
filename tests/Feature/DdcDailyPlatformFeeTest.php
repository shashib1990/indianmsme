<?php

namespace Tests\Feature;

use App\Models\DdcPosOrder;
use App\Models\DdcProfile;
use App\Models\DdcWallet;
use App\Models\DdcWalletRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DdcDailyPlatformFeeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_charges_1_percent_of_previous_day_pos_sales_with_max_100_cap()
    {
        $ddc = User::create([
            'name' => 'Test DDC Fee User',
            'email' => 'ddc_fee_test_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 500.00,
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        // 1. Create POS order for ₹8,000 on yesterday -> 1% is ₹80.00
        $order = DdcPosOrder::create([
            'order_no' => 'ORD/TEST/' . uniqid(),
            'ddc_id' => $ddc->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 1,
            'total_items' => 1,
            'subtotal' => 8000.00,
            'total_amount' => 8000.00,
            'payment_mode' => 'CASH',
            'payment_status' => 'paid',
        ]);
        DB::table('ddc_pos_orders')->where('id', $order->id)->update([
            'created_at' => Carbon::parse($yesterday)->setHour(14),
        ]);

        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);

        $profile->refresh();
        // 500 - 80 = 420.00
        $this->assertEquals(420.00, (float) $profile->wallet_balance);

        $walletTx = DdcWallet::where('ddc_id', $ddc->id)
            ->where('type', 'debit')
            ->latest('id')
            ->first();
        $this->assertNotNull($walletTx);
        $this->assertEquals(80.00, (float) $walletTx->amount);
    }

    public function test_caps_charge_at_100_when_sales_exceed_10000()
    {
        $ddc = User::create([
            'name' => 'Test DDC High Sales',
            'email' => 'ddc_high_sales_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 500.00,
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        // 20,000 sales -> 1% is 200, but capped at 100
        $order = DdcPosOrder::create([
            'order_no' => 'ORD/TEST/' . uniqid(),
            'ddc_id' => $ddc->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 1,
            'total_items' => 1,
            'subtotal' => 20000.00,
            'total_amount' => 20000.00,
            'payment_mode' => 'CASH',
            'payment_status' => 'paid',
        ]);
        DB::table('ddc_pos_orders')->where('id', $order->id)->update([
            'created_at' => Carbon::parse($yesterday)->setHour(14),
        ]);

        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);

        $profile->refresh();
        // 500 - 100 = 400.00
        $this->assertEquals(400.00, (float) $profile->wallet_balance);

        $walletTx = DdcWallet::where('ddc_id', $ddc->id)
            ->where('type', 'debit')
            ->latest('id')
            ->first();
        $this->assertNotNull($walletTx);
        $this->assertEquals(100.00, (float) $walletTx->amount);
    }

    public function test_zero_sales_results_in_zero_fee_deduction()
    {
        $ddc = User::create([
            'name' => 'Test DDC Zero Sales User',
            'email' => 'ddc_zero_sales_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 200.00,
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();
        // No orders created for yesterday

        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);

        $profile->refresh();
        // Balance remains unchanged
        $this->assertEquals(200.00, (float) $profile->wallet_balance);
        $this->assertNull($profile->deactivation_reason);
    }

    public function test_prevents_duplicate_charge_on_same_day()
    {
        $ddc = User::create([
            'name' => 'Test DDC Duplicate Check',
            'email' => 'ddc_dup_check_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 500.00,
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        $order = DdcPosOrder::create([
            'order_no' => 'ORD/TEST/' . uniqid(),
            'ddc_id' => $ddc->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 1,
            'total_items' => 1,
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
            'payment_mode' => 'CASH',
            'payment_status' => 'paid',
        ]);
        DB::table('ddc_pos_orders')->where('id', $order->id)->update([
            'created_at' => Carbon::parse($yesterday)->setHour(14),
        ]);

        // Run 1st time: 1% of 5,000 = ₹50
        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);
        $profile->refresh();
        $this->assertEquals(450.00, (float) $profile->wallet_balance);

        // Run 2nd time: should detect already charged for that date and skip
        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);
        $profile->refresh();
        $this->assertEquals(450.00, (float) $profile->wallet_balance);
    }

    public function test_deactivates_pos_when_wallet_balance_reaches_zero()
    {
        $ddc = User::create([
            'name' => 'Test DDC Low Balance User',
            'email' => 'ddc_exhausted_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 30.00, // Balance less than fee
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        // 5,000 sales -> 1% is ₹50.00. Balance is only ₹30.00!
        $order = DdcPosOrder::create([
            'order_no' => 'ORD/TEST/' . uniqid(),
            'ddc_id' => $ddc->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 1,
            'total_items' => 1,
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
            'payment_mode' => 'CASH',
            'payment_status' => 'paid',
        ]);
        DB::table('ddc_pos_orders')->where('id', $order->id)->update([
            'created_at' => Carbon::parse($yesterday)->setHour(14),
        ]);

        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);

        $profile->refresh();
        $this->assertEquals(0.00, (float) $profile->wallet_balance);
        $this->assertNotNull($profile->deactivation_reason);
        $this->assertStringContainsString('Wallet balance exhausted', $profile->deactivation_reason);

        // POS Checkout must be blocked (HTTP 403)
        $response = $this->actingAs($ddc, 'ddc')->postJson(route('ddc.pos.checkout'), [
            'mode' => 'retail',
            'payment_mode' => 'CASH',
            'customer_name' => 'Test Customer',
            'customer_mobile' => '9876543210',
            'items' => [
                ['inventory_id' => 1, 'qty' => 1]
            ]
        ]);

        $response->assertStatus(403);
        $this->assertFalse($response->json('status'));
        $this->assertStringContainsString('Wallet balance exhausted', $response->json('message'));
    }

    public function test_sends_low_wallet_balance_alert_when_balance_below_or_equal_to_100()
    {
        $ddc = User::create([
            'name' => 'Test DDC Low Alert User',
            'email' => 'ddc_low_alert_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 120.00,
            'per_user_daily_charge' => 100.00,
            'deactivation_reason' => null,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        // 3,000 sales -> 1% is ₹30.00. 120 - 30 = ₹90.00 (<= 100)
        $order = DdcPosOrder::create([
            'order_no' => 'ORD/TEST/' . uniqid(),
            'ddc_id' => $ddc->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 1,
            'total_items' => 1,
            'subtotal' => 3000.00,
            'total_amount' => 3000.00,
            'payment_mode' => 'CASH',
            'payment_status' => 'paid',
        ]);
        DB::table('ddc_pos_orders')->where('id', $order->id)->update([
            'created_at' => Carbon::parse($yesterday)->setHour(14),
        ]);

        Artisan::call('ddc:charge-daily-fee', ['--date' => $yesterday]);

        $profile->refresh();
        $this->assertEquals(90.00, (float) $profile->wallet_balance);

        $notification = Notification::where('recipient_type', 'ddc')
            ->where('recipient_id', $ddc->id)
            ->where('title', 'like', '%Low Wallet Balance%')
            ->latest('id')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Your wallet balance is low', $notification->message);
    }

    public function test_reactivates_when_wallet_is_recharged()
    {
        $ddc = User::create([
            'name' => 'Test DDC Reactivation',
            'email' => 'ddc_reactivate_' . uniqid() . '@example.com',
            'mobile' => '98' . rand(10000000, 99999999),
            'role' => 'ddc',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        $profile = $ddc->ddcProfile ?: DdcProfile::firstOrCreate(['user_id' => $ddc->id]);
        $profile->update([
            'wallet_balance' => 0.00,
            'deactivation_reason' => 'Wallet balance exhausted. Daily platform fee could not be deducted.',
        ]);

        // Create pending wallet request
        $req = DdcWalletRequest::create([
            'ddc_id' => $ddc->id,
            'amount' => 500.00,
            'utr_number' => 'UTR' . rand(100000, 999999),
            'payment_receipt' => 'receipts/test.jpg',
            'status' => 'PENDING',
        ]);

        $admin = User::where('role', 'admin')->first() ?: User::create([
            'name' => 'Test Admin',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'role' => 'admin',
            'status' => '1',
            'password' => Hash::make('password'),
        ]);

        // Call approve route with permission bypass for test
        $response = $this->actingAs($admin, 'admin')->withoutMiddleware(\App\Http\Middleware\CheckPermission::class)->post(route('admin.external.ddc.wallet-requests.approve', $req->id));
        $response->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals(500.00, (float) $profile->wallet_balance);
        $this->assertNull($profile->deactivation_reason);
    }
}
