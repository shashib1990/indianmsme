<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Vendor;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

try {
    $vendor = Vendor::first();
    $brand = Brand::first();
    $category = Category::first();

    if (!$vendor || !$brand || !$category) {
        echo "Missing seeds.\n";
        exit(1);
    }

    // Authenticate as vendor
    auth('vendor')->login($vendor);

    // Create a mock Request
    $request = Request::create('/vendor/products', 'POST', [
        'product_name' => 'Scratch Product Web',
        'barcode' => 'SCRATCH-WEB-' . time(),
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'hsn_code' => '852872',
        'mrp' => 150.00,
        'seller_price_incl_gst' => 100.00,
        'gst_rate' => 18.00,
        'unit_of_measure' => 'pcs',
        'description' => 'Test Product Description',
        'country_of_origin' => 'India',
        'featured_image' => 'http://example.com/image.jpg',
        'submit_approval' => '0',
    ]);

    // Set the session on the request
    $request->setLaravelSession($app['session']->driver());

    echo "Calling ProductController@store...\n";
    $controller = new App\Http\Controllers\Vendor\ProductController();
    $response = $controller->store($request);

    echo "Response status: " . $response->getStatusCode() . "\n";
    if ($response->isRedirection()) {
        echo "Redirected to: " . $response->getTargetUrl() . "\n";
        if (session('errors')) {
            echo "Validation Errors:\n";
            print_r(session('errors')->toArray());
        }
        if (session('error')) {
            echo "Session Error: " . session('error') . "\n";
        }
        if (session('success')) {
            echo "Session Success: " . session('success') . "\n";
        }
    } else {
        echo "Response Content:\n" . $response->getContent() . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
