<?php

namespace App\Services;

use App\Models\AdcInventory;
use App\Models\AdcLocalProduct;
use App\Models\Product;

class BarcodeService
{
    /**
     * Calculate EAN-13 check digit for 12 digits
     */
    public static function calculateEan13CheckDigit(string $digits12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits12[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Calculate EAN-8 check digit for 7 digits
     */
    public static function calculateEan8CheckDigit(string $digits7): int
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (int) $digits7[$i] * ($i % 2 === 0 ? 3 : 1);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Validate EAN-13 format & checksum
     */
    public static function isValidEan13(string $barcode): bool
    {
        if (!preg_match('/^\d{13}$/', $barcode)) {
            return false;
        }
        return (int) $barcode[12] === self::calculateEan13CheckDigit(substr($barcode, 0, 12));
    }

    /**
     * Validate EAN-8 format & checksum
     */
    public static function isValidEan8(string $barcode): bool
    {
        if (!preg_match('/^\d{8}$/', $barcode)) {
            return false;
        }
        return (int) $barcode[7] === self::calculateEan8CheckDigit(substr($barcode, 0, 7));
    }

    /**
     * Validate Code 128 format (alphanumeric, 3-50 chars)
     */
    public static function isValidCode128(string $barcode): bool
    {
        return preg_match('/^[A-Za-z0-9\-\.\_\$\/\+\%]{3,50}$/', $barcode) === 1;
    }

    /**
     * Validate barcode based on chosen format
     */
    public static function validateFormat(string $barcode, string $format = 'CODE128'): array
    {
        $format = strtoupper($format);
        $barcode = trim($barcode);

        if (empty($barcode)) {
            return ['valid' => false, 'message' => 'Barcode cannot be empty.'];
        }

        switch ($format) {
            case 'EAN13':
            case 'EAN-13':
                if (!preg_match('/^\d{13}$/', $barcode)) {
                    return ['valid' => false, 'message' => 'EAN-13 must be exactly 13 digits.'];
                }
                if (!self::isValidEan13($barcode)) {
                    $expected = self::calculateEan13CheckDigit(substr($barcode, 0, 12));
                    return ['valid' => false, 'message' => "Invalid EAN-13 check digit. Expected {$expected} at the end."];
                }
                break;

            case 'EAN8':
            case 'EAN-8':
                if (!preg_match('/^\d{8}$/', $barcode)) {
                    return ['valid' => false, 'message' => 'EAN-8 must be exactly 8 digits.'];
                }
                if (!self::isValidEan8($barcode)) {
                    $expected = self::calculateEan8CheckDigit(substr($barcode, 0, 7));
                    return ['valid' => false, 'message' => "Invalid EAN-8 check digit. Expected {$expected} at the end."];
                }
                break;

            case 'CODE128':
            case 'CODE-128':
            default:
                if (!self::isValidCode128($barcode)) {
                    return ['valid' => false, 'message' => 'Code 128 must be between 3 and 50 alphanumeric characters.'];
                }
                break;
        }

        return ['valid' => true, 'message' => 'Valid barcode.'];
    }

    /**
     * Check if barcode is already assigned to another product under this ADC
     */
    public static function findDuplicate(string $barcode, int $adcId, $excludeId = null, string $type = 'inventory'): ?string
    {
        $barcode = trim($barcode);
        if (empty($barcode) || in_array(strtoupper($barcode), ['N/A', 'NA', '—', '-', '0'])) {
            return null;
        }

        // 1. Check in AdcInventory
        $invQuery = AdcInventory::where('adc_id', $adcId)->where('barcode', $barcode);
        if ($type === 'inventory' && $excludeId) {
            $currentInv = AdcInventory::find($excludeId);
            if ($currentInv) {
                if ($currentInv->product_id) {
                    $invQuery->where(function ($q) use ($currentInv) {
                        $q->whereNull('product_id')->orWhere('product_id', '!=', $currentInv->product_id);
                    });
                } elseif ($currentInv->adc_local_product_id) {
                    $invQuery->where(function ($q) use ($currentInv) {
                        $q->whereNull('adc_local_product_id')->orWhere('adc_local_product_id', '!=', $currentInv->adc_local_product_id);
                    });
                } else {
                    $invQuery->where('id', '!=', $excludeId);
                }
            } else {
                $invQuery->where('id', '!=', $excludeId);
            }
        }
        $inv = $invQuery->first();
        if ($inv) {
            return "Assigned to inventory item '{$inv->product_name}' (Code: " . ($inv->product_code ?: 'N/A') . ")";
        }

        // 2. Check in AdcLocalProduct
        $localQuery = AdcLocalProduct::where('adc_id', $adcId)->where('barcode', $barcode);
        if ($type === 'local_product' && $excludeId) {
            $localQuery->where('id', '!=', $excludeId);
        } elseif ($type === 'inventory' && $excludeId) {
            $currentInv = AdcInventory::find($excludeId);
            if ($currentInv && $currentInv->adc_local_product_id) {
                $localQuery->where('id', '!=', $currentInv->adc_local_product_id);
            }
        }
        $local = $localQuery->first();
        if ($local) {
            return "Assigned to Regional Product '{$local->product_name}' (Code: {$local->local_product_code})";
        }

        // 3. Check in MSME Products
        $msmeQuery = Product::where('barcode', $barcode);
        if ($type === 'inventory' && $excludeId) {
            $currentInv = AdcInventory::find($excludeId);
            if ($currentInv && $currentInv->product_id) {
                $msmeQuery->where('id', '!=', $currentInv->product_id);
            }
        }
        $msmeProd = $msmeQuery->first();
        if ($msmeProd) {
            return "Assigned to MSME Central Product '{$msmeProd->product_name}'";
        }

        return null;
    }

    /**
     * Auto-generate a valid unique barcode
     */
    public static function generate(string $format = 'EAN13', int $adcId = 0): string
    {
        $format = strtoupper($format);

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $code = '';

            if ($format === 'EAN13' || $format === 'EAN-13') {
                // Country prefix: 890 (India) + 9 random digits = 12 digits
                $part12 = '890' . str_pad((string) mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                $checksum = self::calculateEan13CheckDigit($part12);
                $code = $part12 . $checksum;
            } elseif ($format === 'EAN8' || $format === 'EAN-8') {
                // 7 random digits starting with 8
                $part7 = '8' . str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                $checksum = self::calculateEan8CheckDigit($part7);
                $code = $part7 . $checksum;
            } else {
                // Code 128: e.g. 200 + ADC code/timestamp/random = clean 12-14 char alphanumeric
                $code = '890' . date('y') . str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            }

            // Check if not duplicate
            if ($adcId > 0) {
                if (self::findDuplicate($code, $adcId) === null) {
                    return $code;
                }
            } else {
                return $code;
            }
        }

        return $code;
    }
}

