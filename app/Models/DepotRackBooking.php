<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotRackBooking extends Model
{
    use HasFactory;

    protected $table = 'depot_rack_bookings';

    protected $fillable = [
        'booking_no',
        'seller_id',
        'owner_type',
        'owner_id',
        'depot_rack_id',
        'unit_type',
        'quantity',
        'duration_months',
        'start_date',
        'expiry_date',
        'monthly_rate',
        'total_amount',
        'admin_share',
        'depot_share',
        'payment_method',
        'payment_utr',
        'payment_slip',
        'status',
        'seller_remarks',
        'admin_remarks',
        'request_date',
        'action_date',
    ];

    protected $casts = [
        'monthly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'admin_share' => 'decimal:2',
        'depot_share' => 'decimal:2',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'request_date' => 'datetime',
        'action_date' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function depotRack()
    {
        return $this->belongsTo(DepotRack::class, 'depot_rack_id');
    }

    /**
     * Generate unique Booking Number (e.g. RackBook/26-27/000001).
     */
    public static function generateBookingNo(): string
    {
        $month = (int) date('n');
        if ($month >= 4) {
            $fyStart = date('y');
            $fyEnd = date('y', strtotime('+1 year'));
        } else {
            $fyStart = date('y', strtotime('-1 year'));
            $fyEnd = date('y');
        }
        $fyString = "{$fyStart}-{$fyEnd}";
        $prefix = "RackBook/{$fyString}/";

        $latest = self::orderBy('id', 'desc')->first();

        if ($latest) {
            $lastNum = 0;
            if (preg_match('/(\d+)$/', $latest->booking_no, $m)) {
                $lastNum = (int) $m[1];
            }
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check & release expired bookings automatically.
     */
    public static function releaseExpiredBookings(): void
    {
        $today = date('Y-m-d');
        $expiredList = self::where('status', 'Approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->get();

        foreach ($expiredList as $booking) {
            \DB::transaction(function () use ($booking) {
                $booking->status = 'Expired';
                $booking->save();

                $rack = $booking->depotRack;
                if ($rack) {
                    $rack->available_units = min($rack->total_units, $rack->available_units + $booking->quantity);
                    $rack->allotted_units = max(0, $rack->allotted_units - $booking->quantity);
                    $rack->save();
                }
            });
        }
    }
}
