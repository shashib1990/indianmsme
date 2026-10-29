<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiInvoiceExtractorService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;
    protected string $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key');
        $this->model   = config('services.gemini.model', 'gemini-2.5-flash');
        $this->timeout = (int) config('services.gemini.timeout', 60);
    }

    /**
     * Check if Gemini API credentials are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && strlen(trim($this->apiKey)) > 10;
    }

    /**
     * Get the active model name
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Extract structured invoice data from an image or PDF file using Google Gemini API
     *
     * @param string $absoluteFilePath Absolute path to file on disk
     * @param string $mimeType MIME type (image/jpeg, image/png, image/webp, application/pdf)
     * @return array Standardized structured invoice array
     * @throws \Exception
     */
    public function extract(string $absoluteFilePath, string $mimeType): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Gemini API key is not configured. Please set GEMINI_API_KEY in the .env file.');
        }

        if (!file_exists($absoluteFilePath)) {
            throw new \InvalidArgumentException("Invoice file not found at path: {$absoluteFilePath}");
        }

        $fileSize = filesize($absoluteFilePath);
        if ($fileSize <= 0) {
            throw new \InvalidArgumentException("Invoice file is empty (0 bytes): {$absoluteFilePath}");
        }

        // Inline data limit for Gemini REST API is 20MB
        if ($fileSize > 20 * 1024 * 1024) {
            throw new \InvalidArgumentException('Invoice file exceeds the 20MB inline limit for Gemini API.');
        }

        $base64Data = base64_encode(file_get_contents($absoluteFilePath));

        // Define strict response schema for Gemini Structured Output
        $responseSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'supplier_name' => [
                    'type' => 'STRING',
                    'description' => 'Legal or trading business name of the supplier/seller issuing this invoice. Null if absent.',
                ],
                'supplier_mobile' => [
                    'type' => 'STRING',
                    'description' => 'Contact mobile or phone number of the supplier. Null if absent.',
                ],
                'supplier_gstin' => [
                    'type' => 'STRING',
                    'description' => 'GSTIN of the supplier (15-character alphanumeric e.g. 07AAZCA4502F1ZM). Null if absent.',
                ],
                'invoice_number' => [
                    'type' => 'STRING',
                    'description' => 'Unique invoice number / bill number / memo number. Null if absent.',
                ],
                'invoice_date' => [
                    'type' => 'STRING',
                    'description' => 'Date of invoice in YYYY-MM-DD format. Null if absent.',
                ],
                'purchase_date' => [
                    'type' => 'STRING',
                    'description' => 'Purchase date in YYYY-MM-DD format if explicitly stated, otherwise same as invoice date. Null if absent.',
                ],
                'total_amount' => [
                    'type' => 'NUMBER',
                    'description' => 'Grand total / net payable amount of the invoice in numeric format. 0 if absent.',
                ],
                'items' => [
                    'type' => 'ARRAY',
                    'description' => 'List of all product / line item rows in the invoice table.',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'product_name' => [
                                'type' => 'STRING',
                                'description' => 'Complete, clean item description or product title.',
                            ],
                            'product_code' => [
                                'type' => 'STRING',
                                'description' => 'Item code, SKU, or article code if printed (e.g. PROD-1646000204, LCL000287). Null if absent.',
                            ],
                            'barcode' => [
                                'type' => 'STRING',
                                'description' => 'EAN/UPC barcode number (12-14 digits) if printed. Null if absent.',
                            ],
                            'hsn_code' => [
                                'type' => 'STRING',
                                'description' => 'Harmonized System of Nomenclature (HSN) or SAC code (2 to 8 digits). Null if absent.',
                            ],
                            'quantity' => [
                                'type' => 'NUMBER',
                                'description' => 'Quantity purchased. Minimum 1.',
                            ],
                            'unit_of_measure' => [
                                'type' => 'STRING',
                                'description' => 'Unit of measurement e.g. Piece, Box, Bag, Kg, Liter, Packet, Meter. Default Piece.',
                            ],
                            'purchase_price' => [
                                'type' => 'NUMBER',
                                'description' => 'Rate or unit purchase price charged by the supplier before or after tax as stated. 0 if absent.',
                            ],
                            'mrp' => [
                                'type' => 'NUMBER',
                                'description' => 'Maximum Retail Price (MRP) if explicitly printed on the invoice. 0 if missing/wholesale.',
                            ],
                            'csp' => [
                                'type' => 'NUMBER',
                                'description' => 'Customer Sale Price (CSP) if explicitly specified. 0 if missing.',
                            ],
                            'gst_rate' => [
                                'type' => 'NUMBER',
                                'description' => 'GST tax percentage rate e.g. 0, 5, 12, 18, 28. 0 if exempt/unspecified.',
                            ],
                            'line_amount' => [
                                'type' => 'NUMBER',
                                'description' => 'Total amount for this line item (quantity * rate or net taxable value + tax). 0 if absent.',
                            ],
                        ],
                        'required' => ['product_name', 'quantity', 'purchase_price'],
                    ],
                ],
            ],
            'required' => ['items'],
        ];

        $promptText = <<<EOT
You are an expert Indian MSME retail and wholesale invoice auditor.
Carefully examine the provided document (invoice image or PDF).

Extract all structured billing details according to these strict rules:
1. SUPPLIER INFORMATION:
   - Identify the seller/supplier who issued this invoice (look at "Invoice From", "Billed By", letterhead, or logo).
   - Do NOT confuse the supplier with the buyer / customer / consignee ("Bill To", "Ship To").
   - Extract Supplier GSTIN (15 characters) and Supplier contact number if printed.

2. INVOICE METADATA:
   - Extract the invoice number, bill number, or invoice reference.
   - Extract Invoice Date in YYYY-MM-DD format.
   - Extract the Grand Total / Net Payable Amount.

3. PRODUCT ITEMS TABLE:
   - Extract EVERY product row from the table across all pages.
   - For each item, accurately isolate:
     * Product Name: Clean description of goods.
     * Product Code / SKU: e.g. [PROD-1646000204], if present.
     * HSN / SAC Code: 2 to 8 digits.
     * Quantity: Numeric quantity.
     * Unit of Measure: Piece, Box, Bag, Kg, Liter, etc.
     * Purchase Price: The unit rate or purchase price.
     * MRP: Maximum Retail Price if explicitly printed (do NOT guess if absent).
     * GST Rate: Tax percentage (0, 5, 12, 18, 28).
     * Line Total Amount: Total amount for this item.
   - Do NOT swap or confuse Quantity, Rate, MRP, and Line Amount.
   - Do NOT invent or fabricate missing values. If MRP is not stated, use 0.

Output strictly valid JSON conforming to the schema.
EOT;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $promptText],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data'     => $base64Data,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema'   => $responseSchema,
                'temperature'      => 0.1,
            ],
        ];

        $endpoint = "{$this->apiBaseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $startTime = microtime(true);
        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        $duration = round(microtime(true) - $startTime, 2);

        if (!$response->successful()) {
            $status = $response->status();
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? $response->body();

            Log::error("Gemini API Error ({$status}): {$errorMessage}", [
                'model'    => $this->model,
                'duration' => $duration,
            ]);

            throw new \RuntimeException("Gemini API request failed [HTTP {$status}]: {$errorMessage}");
        }

        $responseData = $response->json();
        $rawJson = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($rawJson)) {
            throw new \RuntimeException('Gemini API returned an empty or invalid response.');
        }

        $decoded = json_decode($rawJson, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Failed to decode JSON response from Gemini API.');
        }

        return $this->sanitizeExtractedData($decoded, $duration);
    }

    /**
     * Sanitize, validate and normalize the Gemini extracted data structure
     *
     * @param array $data Raw decoded JSON from Gemini
     * @param float $duration Time taken in seconds
     * @return array Validated and formatted data array
     */
    protected function sanitizeExtractedData(array $data, float $duration): array
    {
        $supplierName = trim($data['supplier_name'] ?? '');
        $supplierMobile = preg_replace('/[^\d]/', '', $data['supplier_mobile'] ?? '');
        if (strlen($supplierMobile) > 10) {
            $supplierMobile = substr($supplierMobile, -10);
        }

        $supplierGstin = strtoupper(trim($data['supplier_gstin'] ?? ''));
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $supplierGstin)) {
            // Check if valid format
            if (strlen($supplierGstin) !== 15) {
                $supplierGstin = '';
            }
        }

        $invoiceNumber = trim($data['invoice_number'] ?? '');
        $invoiceDate   = $this->validateDate($data['invoice_date'] ?? null) ?: date('Y-m-d');
        $purchaseDate  = $this->validateDate($data['purchase_date'] ?? null) ?: $invoiceDate;
        $totalAmount   = (float) ($data['total_amount'] ?? 0.00);

        $rawItems = $data['items'] ?? [];
        $sanitizedItems = [];
        $calculatedItemsTotal = 0.00;

        foreach ($rawItems as $it) {
            $name = trim($it['product_name'] ?? '');
            if (empty($name) || strlen($name) < 2) {
                continue;
            }

            $qty = (float) ($it['quantity'] ?? 1);
            if ($qty <= 0) $qty = 1.0;

            $price = (float) ($it['purchase_price'] ?? 0.00);
            $mrp   = (float) ($it['mrp'] ?? 0.00);
            $csp   = (float) ($it['csp'] ?? 0.00);
            $gst   = (float) ($it['gst_rate'] ?? 0.00);
            $lineAmt = (float) ($it['line_amount'] ?? round($qty * $price, 2));

            if ($lineAmt <= 0) {
                $lineAmt = round($qty * $price, 2);
            }

            $calculatedItemsTotal += $lineAmt;

            // Compute CSP if absent but MRP and Price are known:
            // CSP = purchase_price + ((mrp - purchase_price) / 2)
            $isCspMissing = false;
            $isMrpMissing = false;

            if ($csp <= 0 && $mrp > $price && $price > 0) {
                $csp = round($price + (($mrp - $price) / 2), 2);
            } elseif ($csp <= 0) {
                $isCspMissing = true;
            }

            if ($mrp <= 0) {
                $isMrpMissing = true;
            }

            $unit = trim($it['unit_of_measure'] ?? 'Piece');
            if (empty($unit)) $unit = 'Piece';

            $sanitizedItems[] = [
                'product_name'    => $name,
                'product_code'    => !empty($it['product_code']) ? trim($it['product_code']) : null,
                'barcode'         => !empty($it['barcode']) ? trim($it['barcode']) : null,
                'hsn_code'        => !empty($it['hsn_code']) ? trim($it['hsn_code']) : null,
                'gst_rate'        => $gst,
                'unit_of_measure' => ucfirst(strtolower($unit)),
                'quantity'        => $qty,
                'purchase_price'  => $price,
                'mrp'             => $mrp,
                'csp'             => $csp,
                'line_amount'     => $lineAmt,
                'is_reconciled'   => (abs(round($qty * $price, 2) - $lineAmt) <= 2.0),
                'is_mrp_missing'  => $isMrpMissing,
                'is_csp_missing'  => $isCspMissing,
            ];
        }

        $calculatedItemsTotal = round($calculatedItemsTotal, 2);
        $diff = round(abs($calculatedItemsTotal - $totalAmount), 2);
        $isReconciled = ($totalAmount > 0) ? ($diff <= 2.0) : true;

        $note = $isReconciled
            ? "Reconciled with stated invoice total (₹" . number_format($totalAmount, 2) . ")"
            : "Difference of ₹" . number_format($diff, 2) . " between calculated items total (₹" . number_format($calculatedItemsTotal, 2) . ") and stated grand total (₹" . number_format($totalAmount, 2) . ")";

        return [
            'engine'              => 'gemini',
            'engine_label'        => 'Google Gemini AI (' . $this->model . ')',
            'duration_seconds'    => $duration,
            'supplier_name'       => $supplierName,
            'supplier_mobile'     => $supplierMobile,
            'supplier_gstin'      => $supplierGstin,
            'invoice_number'      => $invoiceNumber,
            'invoice_date'        => $invoiceDate,
            'purchase_date'       => $purchaseDate,
            'total_amount'        => $totalAmount,
            'products'            => $sanitizedItems,
            'is_reconciled'       => $isReconciled,
            'items_calculated'    => $calculatedItemsTotal,
            'reconciliation_diff' => $diff,
            'reconciliation_note' => $note,
            'raw_line_count'      => count($sanitizedItems),
        ];
    }

    /**
     * Helper to validate date string
     */
    protected function validateDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;
        $ts = strtotime($dateStr);
        if ($ts === false || $ts < strtotime('2000-01-01') || $ts > strtotime('+1 year')) {
            return null;
        }
        return date('Y-m-d', $ts);
    }
}
