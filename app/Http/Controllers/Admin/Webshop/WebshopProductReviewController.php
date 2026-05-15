<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductReview;
use Illuminate\Http\Request;

class WebshopProductReviewController extends AdminExtendedController
{
    public function index(WebshopProduct $product)
    {
        $reviews = $product->reviews()->latestFirst()->get();
        return view('admin.webshop.product-reviews.index', [
            'product' => $product,
            'reviews' => $reviews,
        ]);
    }

    public function destroy(WebshopProduct $product, WebshopProductReview $review)
    {
        $review->delete();
        return redirect()->route('admin.webshop.products.reviews.index', $product)->with('success', 'Vélemény sikeresen törölve.');
    }

    public function toggleActive(Request $request)
    {
        $r = WebshopProductReview::findOrFail($request->input('id'));
        $r->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $r->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }
}
