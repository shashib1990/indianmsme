<?php

namespace App\Services;

use App\Models\AdcPosCustomer;
use App\Models\AdcPosOrder;
use App\Models\AdcPosSalesReturn;
use App\Models\AdcCustomerPayment;
use App\Models\AdcCustomerCreditLedger;
use App\Services\TheekHaiService;
use Illuminate\Support\Facades\DB;
use Exception;

class AdcCreditService
{
    /**
     * Record a Credit Sale for ADC POS Order
     */
    public static function recordCreditSale(AdcPosOrder $order, ?string $referenceNo = null): AdcCustomerCreditLedger
    {
        if (!$order->customer_id) {
            throw new Exception("Please select a customer to create credit.");
        }

        return DB::transaction(function () use ($order, $referenceNo) {
            $customer = AdcPosCustomer::where('id', $order->customer_id)->lockForUpdate()->firstOrFail();

            $debitAmount = round((float) $order->total_amount, 2);
            $prevBalance = round((float) $customer->outstanding_balance, 2);
            $newBalance = round($prevBalance + $debitAmount, 2);
            $refNo = $referenceNo ?: ($order->invoice?->invoice_number ?: $order->order_no);

            $ledger = AdcCustomerCreditLedger::create([
                'adc_id'           => $order->adc_id,
                'customer_id'      => $customer->id,
                'pos_order_id'     => $order->id,
                'payment_id'       => null,
                'sales_return_id'  => null,
                'reference_no'     => $refNo,
                'transaction_type' => 'credit_sale',
                'debit'            => $debitAmount,
                'credit'           => 0.00,
                'payment_mode'     => 'credit',
                'balance'          => $newBalance,
                'remarks'          => 'POS Credit Sale #' . $refNo,
                'created_by'       => $order->adc_id,
            ]);

            $customer->outstanding_balance = $newBalance;
            $customer->total_credit_given = round((float) $customer->total_credit_given + $debitAmount, 2);
            $customer->save();

            // Fetch billing ADC user details to get sender mobile number for 'from' field
            $adcUser = \App\Models\User::find($order->adc_id);
            $adcMobile = $adcUser?->mobile ?: ($adcUser?->adcProfile?->phone ?: $adcUser?->adcProfile?->official_phone);

            // Register ADC Customer mobile number via TheekHai Register API
            if (!empty($customer->mobile)) {
                TheekHaiService::registerUser($customer->name ?: 'Customer', $customer->mobile);

                // Send Credit Bill SMS Notification with ADC mobile as 'from'
                $custName = $customer->name ?: 'Customer';
                $message = "Dear {$custName}, your credit bill #{$refNo} of Rs. " . number_format($debitAmount, 2) . " has been created. Total outstanding credit balance: Rs. " . number_format($newBalance, 2) . ". Thank you!";
                TheekHaiService::sendMessage($customer->mobile, $message, $adcMobile);
            }

            return $ledger;
        });
    }

    /**
     * Record Payment Received from Customer (Udhaar Jama)
     */
    public static function recordPaymentReceived(
        int $adcId,
        int $customerId,
        float $amount,
        string $paymentMode,
        ?string $remarks = null,
        ?int $createdBy = null
    ): AdcCustomerPayment {
        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        $modeUpper = strtoupper(trim($paymentMode));
        if ($modeUpper === 'CARD') {
            $formattedMode = 'Card';
        } elseif ($modeUpper === 'UPI') {
            $formattedMode = 'UPI';
        } else {
            $formattedMode = 'Cash';
        }

        return DB::transaction(function () use ($adcId, $customerId, $amount, $formattedMode, $remarks, $createdBy) {
            $customer = AdcPosCustomer::where('id', $customerId)
                ->where('adc_id', $adcId)
                ->lockForUpdate()
                ->firstOrFail();

            $creditAmount = round($amount, 2);
            $prevBalance = round((float) $customer->outstanding_balance, 2);
            $newBalance = round($prevBalance - $creditAmount, 2);

            $paymentNumber = AdcCustomerPayment::generatePaymentNumber();

            $payment = AdcCustomerPayment::create([
                'payment_number' => $paymentNumber,
                'adc_id'         => $adcId,
                'customer_id'    => $customer->id,
                'amount'         => $creditAmount,
                'payment_mode'   => $formattedMode,
                'payment_date'   => now(),
                'remarks'        => $remarks,
                'created_by'     => $createdBy ?: $adcId,
            ]);

            AdcCustomerCreditLedger::create([
                'adc_id'           => $adcId,
                'customer_id'      => $customer->id,
                'pos_order_id'     => null,
                'payment_id'       => $payment->id,
                'sales_return_id'  => null,
                'reference_no'     => $paymentNumber,
                'transaction_type' => 'payment',
                'debit'            => 0.00,
                'credit'           => $creditAmount,
                'payment_mode'     => strtolower($formattedMode),
                'balance'          => $newBalance,
                'remarks'          => $remarks ?: 'Payment received via ' . $formattedMode,
                'created_by'       => $createdBy ?: $adcId,
            ]);

            $customer->outstanding_balance = $newBalance;
            $customer->total_payment_received = round((float) $customer->total_payment_received + $creditAmount, 2);
            $customer->save();

            // Fetch receiving ADC user details to get sender mobile number for 'from' field
            $adcUser = \App\Models\User::find($adcId);
            $adcMobile = $adcUser?->mobile ?: ($adcUser?->adcProfile?->phone ?: $adcUser?->adcProfile?->official_phone);

            // Send Credit Repayment SMS Notification with ADC mobile as 'from'
            if (!empty($customer->mobile)) {
                $custName = $customer->name ?: 'Customer';
                $message = "Dear {$custName}, payment of Rs. " . number_format($creditAmount, 2) . " received against your credit account (Ref: {$paymentNumber}). Remaining outstanding credit balance: Rs. " . number_format($newBalance, 2) . ". Thank you!";
                TheekHaiService::sendMessage($customer->mobile, $message, $adcMobile);
            }

            return $payment;
        });
    }

    /**
     * Record Sales Return adjustment on Customer Credit Ledger
     */
    public static function recordSalesReturnCredit(AdcPosSalesReturn $salesReturn): ?AdcCustomerCreditLedger
    {
        if (!$salesReturn->customer_id) {
            return null;
        }

        return DB::transaction(function () use ($salesReturn) {
            $customer = AdcPosCustomer::where('id', $salesReturn->customer_id)->lockForUpdate()->first();
            if (!$customer) {
                return null;
            }

            $creditAmount = round((float) $salesReturn->total_return_amount, 2);
            $prevBalance = round((float) $customer->outstanding_balance, 2);
            $newBalance = round($prevBalance - $creditAmount, 2);

            $ledger = AdcCustomerCreditLedger::create([
                'adc_id'           => $salesReturn->adc_id,
                'customer_id'      => $customer->id,
                'pos_order_id'     => $salesReturn->pos_order_id,
                'payment_id'       => null,
                'sales_return_id'  => $salesReturn->id,
                'reference_no'     => $salesReturn->return_number,
                'transaction_type' => 'Sales Return',
                'debit'            => 0.00,
                'credit'           => $creditAmount,
                'payment_mode'     => $salesReturn->refund_mode ?: 'Store Credit',
                'balance'          => $newBalance,
                'remarks'          => 'Sales Return Reversal #' . $salesReturn->return_number,
                'created_by'       => $salesReturn->adc_id,
            ]);

            $customer->outstanding_balance = $newBalance;
            $customer->save();

            return $ledger;
        });
    }
}
