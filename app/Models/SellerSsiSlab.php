<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerSsiSlab extends Model
{
    use HasFactory;

    protected $table = 'seller_ssi_slabs';

    protected $fillable = [
        'seller_id',
        'min_amount',
        'max_amount',
        'percentage',
    ];

    protected $casts = [
        'min_amount' => 'float',
        'max_amount' => 'float',
        'percentage' => 'float',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Resolve matching SSI percentage for a seller and shopping total amount.
     */
    public static function resolvePercentage(?int $sellerId, float $amount): float
    {
        if (!$sellerId) {
            return 0.00;
        }

        $slabs = self::where('seller_id', $sellerId)
            ->orderBy('min_amount', 'asc')
            ->get();

        if ($slabs->isEmpty()) {
            $profile = SellerProfile::where('user_id', $sellerId)->first();
            $customPct = (float) ($profile?->ddc_ama_commission_percentage ?? 0);
            return $customPct > 0 ? $customPct : 0.00;
        }

        foreach ($slabs as $slab) {
            $min = (float) $slab->min_amount;
            $max = $slab->max_amount !== null ? (float) $slab->max_amount : null;

            if ($amount >= $min && ($max === null || $amount <= $max)) {
                return (float) $slab->percentage;
            }
        }

        // If amount is lower than lowest slab min_amount
        $firstSlab = $slabs->first();
        if ($amount < (float)$firstSlab->min_amount) {
            return (float) $firstSlab->percentage;
        }

        // If amount is higher than highest max_amount
        $lastSlab = $slabs->last();
        return (float) $lastSlab->percentage;
    }
}
