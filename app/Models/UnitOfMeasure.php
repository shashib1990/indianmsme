<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $table = 'unit_of_measures';

    protected $fillable = [
        'name',
        'short_code',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active units array list of names with standard fallbacks if table is empty.
     */
    public static function getUnitsList(): array
    {
        try {
            $units = self::where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();

            if (!empty($units)) {
                return $units;
            }
        } catch (\Throwable $e) {
            // Fallback if query fails
        }

        return [
            'Piece', 'Box', 'Pack', 'Bottle', 'Kg', 'Gram',
            'Litre', 'ML', 'Pair', 'Set', 'Meter', 'Dozen', 'Container', 'Other'
        ];
    }

    /**
     * Check if decimal quantity is allowed for the given UOM string.
     */
    public static function isDecimalAllowed(?string $uom): bool
    {
        if (!$uom) {
            return false;
        }
        $lower = strtolower(trim($uom));
        $decimalUnits = [
            'kg', 'kilo', 'kilogram', 
            'gm', 'gram', 'gramme', 
            'liter', 'litre', 'ltr', 
            'ml', 'milliliter', 
            'gallon', 'loose', 
            'meter', 'mtr', 
            'ton', 'tonne', 'quintal'
        ];
        foreach ($decimalUnits as $unit) {
            if (str_contains($lower, $unit)) {
                return true;
            }
        }
        return false;
    }
}

