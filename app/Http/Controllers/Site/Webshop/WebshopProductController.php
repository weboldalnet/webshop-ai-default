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

        $relations = ['category', 'relatedProducts', 'variations', 'productProperties.property'];
        
        if (WebshopSettingsService::getBool('product_extra_gallery_enabled')) {
            $relations[] = 'defaultGalleryImages';
            $relations[] = 'secondaryGalleryImages';
        } else {
            $relations[] = 'defaultGalleryImages';
        }

        if (WebshopSettingsService::getBool('product_document_upload_enabled')) {
            $relations['productDocuments'] = function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            };
        }

        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $relations[] = 'label';
        }

        $product->load($relations);

        $similarQuery = WebshopProduct::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(8);

        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $similarQuery->with('label');
        }

        $similarProducts = $similarQuery->get();

        return view('site.webshop.products.show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
            'isProductPage' => true,
        ]);
    }
}
