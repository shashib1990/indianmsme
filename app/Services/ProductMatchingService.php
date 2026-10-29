<?php

namespace App\Services;

use App\Models\AdcLocalProduct;
use App\Models\AdcInventory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductMatchingService
{
    /**
     * Cache existing local products, inventories, central products, and brands
     */
    protected ?Collection $localProducts = null;
    protected ?Collection $inventories = null;
    protected ?Collection $brands = null;
    protected ?Collection $categories = null;

    /**
     * Match a list of extracted product rows against existing product masters
     *
     * @param array $extractedProducts
     * @param int $adcId
     * @return array
     */
    public function matchProducts(array $extractedProducts, int $adcId): array
    {
        // Load ADC's local products
        $this->localProducts = AdcLocalProduct::where('adc_id', $adcId)
            ->with(['category', 'brand'])
            ->get();

        // Load ADC's distinct inventory products
        $this->inventories = AdcInventory::where('adc_id', $adcId)
            ->select('id', 'product_name', 'product_code', 'barcode', 'unit_of_measure', 'mrp', 'csp', 'purchase_price', 'gst_rate')
            ->get();

        $this->brands = Brand::active()->select('id', 'name')->get();
        $this->categories = Category::active()->select('id', 'name')->get();

        $matchedRows = [];

        foreach ($extractedProducts as $item) {
            $matchedRows[] = $this->matchSingleItem($item, $adcId);
        }

        return $matchedRows;
    }

    /**
     * Match a single item row with layout-aware attributes and multiple candidate suggestions
     */
    protected function matchSingleItem(array $item, int $adcId): array
    {
        $rawName     = trim($item['product_name'] ?? '');
        $productCode = !empty($item['product_code']) ? trim($item['product_code']) : null;
        $barcode     = !empty($item['barcode']) ? trim($item['barcode']) : null;
        $hsn         = !empty($item['hsn_code']) ? trim($item['hsn_code']) : null;

        $normalizedName = $this->normalizeName($rawName);

        $matchedProduct = null;
        $matchType = 'NEW';
        $confidence = 0;
        $source = '';
        $possibleMatches = [];

        // 1. Try Exact Product Code match in ADC Local Products, Inventories, or Central MSME Product Master
        if ($productCode && !$matchedProduct) {
            $matchedProduct = $this->localProducts->firstWhere('local_product_code', $productCode);
            if ($matchedProduct) {
                $matchType = 'EXACT';
                $confidence = 100;
                $source = "Code Match ({$productCode})";
            } else {
                $invMatch = $this->inventories->firstWhere('product_code', $productCode);
                if ($invMatch) {
                    $matchedProduct = $invMatch;
                    $matchType = 'EXACT';
                    $confidence = 98;
                    $source = "Inventory Code Match ({$productCode})";
                } else {
                    $central = Product::where('product_code', $productCode)->first();
                    if ($central) {
                        $matchedProduct = $this->convertCentralToMatchData($central);
                        $matchType = 'EXACT';
                        $confidence = 99;
                        $source = "Central Master Code Match ({$productCode})";
                    }
                }
            }
        }

        // 2. Try Exact Barcode match in ADC Local Products
        if ($barcode && !$matchedProduct) {
            $matchedProduct = $this->localProducts->firstWhere('barcode', $barcode);
            if ($matchedProduct) {
                $matchType = 'EXACT';
                $confidence = 100;
                $source = "Barcode Match ({$barcode})";
            }
        }

        // 3. Try Exact Barcode match in Central MSME Product Master
        if ($barcode && !$matchedProduct) {
            $central = Product::where('barcode', $barcode)->first();
            if ($central) {
                $matchedProduct = $this->convertCentralToMatchData($central);
                $matchType = 'EXACT';
                $confidence = 98;
                $source = 'Central Master Barcode Match';
            }
        }

        // 4. Exact Normalized Name Match in ADC Local Products
        if (!$matchedProduct && !empty($normalizedName)) {
            foreach ($this->localProducts as $local) {
                if ($this->normalizeName($local->product_name) === $normalizedName) {
                    $matchedProduct = $local;
                    $matchType = 'EXACT';
                    $confidence = 95;
                    $source = 'Exact Name Match';
                    break;
                }
            }
        }

        // 5. Exact Normalized Name Match in Central MSME Products
        if (!$matchedProduct && !empty($normalizedName)) {
            $central = Product::whereRaw('LOWER(TRIM(product_name)) = ?', [$normalizedName])->first();
            if ($central) {
                $matchedProduct = $this->convertCentralToMatchData($central);
                $matchType = 'EXACT';
                $confidence = 92;
                $source = 'Central Master Exact Name Match';
            }
        }

        // 6. Fuzzy Name Similarity Search across Local Products & Central Catalog
        $candidates = [];

        // Check local products for candidates
        foreach ($this->localProducts as $local) {
            $score = $this->calculateSimilarity($normalizedName, $this->normalizeName($local->product_name));
            if ($score >= 65) {
                $candidates[] = [
                    'id'          => $local->id,
                    'is_local'    => true,
                    'code'        => $local->local_product_code,
                    'name'        => $local->product_name,
                    'similarity'  => round($score),
                    'category_id' => $local->category_id,
                    'brand_id'    => $local->brand_id,
                    'gst_rate'    => (float) $local->gst_rate,
                    'unit'        => $local->unit_of_measure,
                    'model'       => $local,
                ];
            }
        }

        // If local had few matches, also search central products
        if (count($candidates) < 5 && strlen($normalizedName) >= 4) {
            $prefix = substr($normalizedName, 0, 4);
            $centralMatches = Product::where('product_name', 'like', "%{$prefix}%")->limit(15)->get();
            foreach ($centralMatches as $cm) {
                $score = $this->calculateSimilarity($normalizedName, $this->normalizeName($cm->product_name));
                if ($score >= 65) {
                    $candidates[] = [
                        'id'          => 'CENTRAL_' . $cm->id,
                        'is_local'    => false,
                        'code'        => $cm->product_code,
                        'name'        => $cm->product_name,
                        'similarity'  => round($score),
                        'category_id' => $cm->category_id,
                        'brand_id'    => $cm->brand_id,
                        'gst_rate'    => (float) $cm->gst_rate,
                        'unit'        => $cm->unit_of_measure,
                        'model'       => $this->convertCentralToMatchData($cm),
                    ];
                }
            }
        }

        // Sort candidates by similarity descending
        usort($candidates, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $possibleMatches = array_slice($candidates, 0, 5);

        // If not exact matched yet, use the top candidate if similarity >= 80%
        if (!$matchedProduct && !empty($possibleMatches) && $possibleMatches[0]['similarity'] >= 80) {
            $topCand = $possibleMatches[0];
            $matchedProduct = $topCand['model'];
            $matchType = 'SUGGESTED';
            $confidence = $topCand['similarity'];
            $source = ($topCand['is_local'] ? 'Local Master' : 'Central Master') . " ({$confidence}%)";
        }

        // Detect or suggest Brand from product name
        $suggestedBrandId = null;
        if ($matchedProduct && isset($matchedProduct->brand_id)) {
            $suggestedBrandId = $matchedProduct->brand_id;
        } else {
            $suggestedBrandId = $this->detectBrandFromName($rawName);
        }

        // Detect or suggest Category
        $suggestedCategoryId = null;
        if ($matchedProduct && isset($matchedProduct->category_id)) {
            $suggestedCategoryId = $matchedProduct->category_id;
        } else {
            $suggestedCategoryId = $this->detectCategoryFromName($rawName);
        }

        // Final values prioritizing invoice values first, falling back to matched data
        $finalCategoryId = $suggestedCategoryId ?: ($this->categories->first()?->id ?: 1);
        $finalBrandId    = $suggestedBrandId;
        $finalHsn        = !empty($hsn) ? $hsn : ($matchedProduct->hsn_code ?? null);
        $finalGst        = ($item['gst_rate'] > 0) ? $item['gst_rate'] : ($matchedProduct->gst_rate ?? 0.00);
        $finalUnit       = !empty($item['unit_of_measure']) ? $item['unit_of_measure'] : ($matchedProduct->unit_of_measure ?? 'Piece');
        $finalPrice      = (float) ($item['purchase_price'] ?? 0.00);

        // MRP: if extracted from invoice use it, otherwise suggest from matched product (flagged if missing)
        $invoiceMrp = (float) ($item['mrp'] ?? 0.00);
        $finalMrp = $invoiceMrp > 0 ? $invoiceMrp : (float) ($matchedProduct->mrp ?? 0.00);
        $isMrpMissing = ($finalMrp <= 0);

        // CSP: calculate ONLY if both MRP and Purchase Price are present
        $finalCsp = (float) ($item['csp'] ?? 0.00);
        if ($finalCsp <= 0 && $finalMrp > $finalPrice && $finalPrice > 0) {
            $finalCsp = round($finalPrice + (($finalMrp - $finalPrice) / 2), 2);
        }
        $isCspMissing = ($finalCsp <= 0);

        // Determine local_product_id dropdown selection value
        $selectedProductId = 'NEW';
        if ($matchedProduct) {
            if ($matchedProduct instanceof AdcLocalProduct) {
                $selectedProductId = (string) $matchedProduct->id;
            } elseif ($matchedProduct instanceof AdcInventory) {
                $selectedProductId = 'INV_' . $matchedProduct->id;
            }
        }

        return [
            'raw_name'           => $rawName,
            'product_name'       => $matchedProduct ? $matchedProduct->product_name : $rawName,
            'match_type'         => $matchType,
            'confidence'         => $confidence,
            'match_source'       => $source,
            'local_product_id'   => $selectedProductId,
            'product_code'       => $productCode ?: ($matchedProduct->local_product_code ?? ($matchedProduct->product_code ?? null)),
            'barcode'            => $barcode ?: ($matchedProduct->barcode ?? null),
            'category_id'        => $finalCategoryId,
            'brand_id'           => $finalBrandId,
            'hsn_code'           => $finalHsn,
            'gst_rate'           => $finalGst,
            'unit_of_measure'    => $finalUnit,
            'quantity'           => $item['quantity'] ?? 1,
            'purchase_price'     => $finalPrice,
            'mrp'                => $finalMrp,
            'csp'                => $finalCsp,
            'line_amount'        => (float) ($item['line_amount'] ?? round(($item['quantity'] ?? 1) * $finalPrice, 2)),
            'is_reconciled'      => (bool) ($item['is_reconciled'] ?? false),
            'is_mrp_missing'     => $isMrpMissing,
            'is_csp_missing'     => $isCspMissing,
            'possible_matches'   => array_map(function($c) {
                return [
                    'id'         => $c['id'],
                    'code'       => $c['code'],
                    'name'       => $c['name'],
                    'similarity' => $c['similarity'],
                ];
            }, $possibleMatches),
        ];
    }

    /**
     * Normalize name for case and symbol-insensitive matching
     */
    protected function normalizeName(string $name): string
    {
        $name = strtolower($name);
        // Strip common noise like "(Multi Colour)", "[LCL...]", "MRP..."
        $name = preg_replace('/\[[a-z0-9_\-]+\]/i', '', $name);
        $name = preg_replace('/mrp\s*[\d,]+/i', '', $name);
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Calculate string similarity percentage using combination of similar_text and token Jaccard
     */
    protected function calculateSimilarity(string $s1, string $s2): float
    {
        if ($s1 === $s2) return 100.0;
        if (empty($s1) || empty($s2)) return 0.0;

        similar_text($s1, $s2, $percent);

        // Also check token intersection
        $t1 = array_filter(explode(' ', $s1));
        $t2 = array_filter(explode(' ', $s2));

        if (!empty($t1) && !empty($t2)) {
            $intersection = count(array_intersect($t1, $t2));
            $union = count(array_unique(array_merge($t1, $t2)));
            $jaccard = ($intersection / $union) * 100;
            // Weighted average
            $combined = ($percent * 0.6) + ($jaccard * 0.4);
            return max($percent, $combined);
        }

        return $percent;
    }

    /**
     * Convert central Product model to match data object
     */
    protected function convertCentralToMatchData(Product $p): object
    {
        return (object)[
            'is_central'         => true,
            'id'                 => null,
            'msme_product_id'    => $p->id,
            'product_name'       => $p->product_name,
            'product_code'       => $p->product_code,
            'barcode'            => $p->barcode,
            'category_id'        => $p->category_id,
            'brand_id'           => $p->brand_id,
            'hsn_code'           => $p->hsn_code,
            'gst_rate'           => $p->gst_rate,
            'unit_of_measure'    => $p->unit_of_measure,
            'mrp'                => $p->mrp,
        ];
    }

    /**
     * Detect Brand ID if a brand name appears inside the product name
     */
    protected function detectBrandFromName(string $name): ?int
    {
        $lowerName = strtolower($name);
        foreach ($this->brands as $brand) {
            $bName = strtolower(trim($brand->name));
            if (strlen($bName) >= 3 && preg_match('/\b' . preg_quote($bName, '/') . '\b/', $lowerName)) {
                return $brand->id;
            }
        }
        return null;
    }

    /**
     * Detect Category ID if category name appears in product name
     */
    protected function detectCategoryFromName(string $name): ?int
    {
        $lowerName = strtolower($name);
        foreach ($this->categories as $category) {
            $cName = strtolower(trim($category->name));
            if (strlen($cName) >= 4 && str_contains($lowerName, $cName)) {
                return $category->id;
            }
        }
        return null;
    }
}
