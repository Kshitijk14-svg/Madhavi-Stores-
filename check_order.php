<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = App\Models\Product::all();
try {
    if ($products->count() > 0) {
        $product = $products->first();
        echo "Rendering product: " . $product->slug . "\n";
        $view = view('pages.product-show', [
            'product' => $product,
            'relatedProducts' => App\Models\Product::take(4)->get(),
            'hasPurchased' => false,
            'wishlistIds' => []
        ])->render();
        echo "RENDERING_SUCCESSFUL\n";
    }
} catch (\Exception $e) {
    echo "RENDERING_ERROR:" . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
