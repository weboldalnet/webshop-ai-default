<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopProductController extends Controller
{
    public function show(WebshopProduct $product)
    {
        if (!$product->is_active) abort(404);

        $product->load(['category', 'galleryImages', 'relatedProducts', 'variations', 'productProperties.property']);

        $similarProducts = WebshopProduct::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(8)
            ->get();

        return view('site.webshop.products.show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
            'ws' => WebshopSettingsService::all()
        ]);
    }
}
