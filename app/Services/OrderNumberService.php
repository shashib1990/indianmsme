<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    /**
     * Generate a unique global Financial Year-wise Order Number in ORD/26-27/000001 format.
     * Sequence resets to 000001 on 1 April - 31 March of every Financial Year.
     * Managed centrally in the `order_sequences` table with row locking to ensure thread-safety.
     *
     * @param string|\DateTimeInterface|null $date
     * @return string
     */
    public static function generateOrderNumber($date = null): string
    {
        $carbonDate = $date ? Carbon::parse($date) : now();
        $year = $carbonDate->year;

        // Indian Financial Year: April 1st to March 31st
        if ($carbonDate->month < 4) {
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            $startYear = $year;
            $endYear = $year + 1;
        }

        $fyStr = sprintf('%02d-%02d', $startYear % 100, $endYear % 100);

        return DB::transaction(function () use ($fyStr) {
            // Lock sequence record for current financial year
            $sequenceRecord = DB::table('order_sequences')
                ->where('financial_year', $fyStr)
                ->lockForUpdate()
                ->first();

            if (!$sequenceRecord) {
                // If sequence record does not exist yet for this FY, seed from max existing orders
                $maxAdcSerial = 0;
                $adcOrders = DB::table('adc_orders')
                    ->where('order_number', 'like', 'ORD/' . $fyStr . '/%')
                    ->pluck('order_number');

                foreach ($adcOrders as $ordNum) {
                    if (preg_match('/\/(\d+)$/', $ordNum, $matches)) {
                        $num = (int) $matches[1];
                        if ($num > $maxAdcSerial) $maxAdcSerial = $num;
                    }
                }

                $maxPosSerial = 0;
                $posOrders = DB::table('ddc_pos_orders')
                    ->where('order_no', 'like', 'ORD/' . $fyStr . '/%')
                    ->pluck('order_no');

                foreach ($posOrders as $ordNo) {
                    if (preg_match('/\/(\d+)$/', $ordNo, $matches)) {
                        $num = (int) $matches[1];
                        if ($num > $maxPosSerial) $maxPosSerial = $num;
                    }
                }

                $maxExisting = max($maxAdcSerial, $maxPosSerial);
                $nextSerial = $maxExisting + 1;
                $orderNumber = 'ORD/' . $fyStr . '/' . sprintf('%06d', $nextSerial);

                DB::table('order_sequences')->insert([
                    'financial_year'    => $fyStr,
                    'last_sequence'     => $nextSerial,
                    'last_order_number' => $orderNumber,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                return $orderNumber;
            }

            $nextSerial = $sequenceRecord->last_sequence + 1;
            $orderNumber = 'ORD/' . $fyStr . '/' . sprintf('%06d', $nextSerial);

            DB::table('order_sequences')
                ->where('id', $sequenceRecord->id)
                ->update([
                    'last_sequence'     => $nextSerial,
                    'last_order_number' => $orderNumber,
                    'updated_at'        => now(),
                ]);

            return $orderNumber;
        });
    }
}
