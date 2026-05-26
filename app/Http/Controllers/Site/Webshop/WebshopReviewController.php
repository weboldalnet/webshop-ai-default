<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductReview;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopReviewController extends Controller
{
    public function store(Request $request)
    {
        if (!WebshopSettingsService::getBool('site_product_reviews_enabled')) {
            return response()->json(['success' => false, 'message' => 'A véleményezés nem engedélyezett.'], 403);
        }

        $request->validate([
            'product_id' => 'required|exists:webshop_products,id',
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        WebshopProductReview::create([
            'product_id' => $request->input('product_id'),
            'name' => $request->input('name'),
            'rating' => $request->input('rating'),
            'review' => $request->input('review'),
            'is_active' => true, // Alapértelmezetten aktív a specifikáció szerint (ha nincs más jelezve)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Köszönjük a véleményét.',
        ]);
    }
}
