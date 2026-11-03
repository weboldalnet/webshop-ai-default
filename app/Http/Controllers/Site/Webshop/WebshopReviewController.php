<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductReview;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopReviewService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopReviewController extends Controller
{
    /**
     * Vélemény beküldése (AJAX, a termékoldal modaljából).
     *
     * A választ a meglévő site JS (webshop-site.js) dolgozza fel: siker esetén a
     * `message`-et jeleníti meg, hibánál a 422-es válaszra általános hibaüzenetet ad.
     * Ezért ellenőrzési hibánál validációs kivételt dobunk (422), nem success:false-t –
     * arra a JS nem reagálna. A JS-hez szándékosan nem nyúlunk: a projektekbe
     * közzétett példánya frissítéskor nem cserélődik.
     */
    public function store(Request $request)
    {
        if (!WebshopSettingsService::getBool('site_product_reviews_enabled')) {
            return response()->json(['success' => false, 'message' => __('webshop::product.reviews.not_allowed')], 403);
        }

        $verifiedOnly = WebshopReviewService::verifiedOnly();

        $request->validate([
            // modell-osztállyal: a termék közös (alapnyelvi) adatbázisában ellenőriz, nem a látogató nyelvi DB-jében
            'product_id' => ['required', \Illuminate\Validation\Rule::exists(\Weboldalnet\WebshopAiDefault\Models\WebshopProduct::class, 'id')],
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
            // A vásárlás igazolása: csak-igazolt módban kötelező, egyébként opcionális,
            // de ha az egyiket megadja, a másik is kell.
            'order_number' => ($verifiedOnly ? 'required' : 'nullable|required_with:order_email') . '|string|max:64',
            'order_email' => ($verifiedOnly ? 'required' : 'nullable|required_with:order_number') . '|email|max:255',
        ], [
            // A projekt validációs nyelve nem mindig magyar – ezeket a vásárló látja
            'order_number.required' => __('webshop::product.review_validation.order_number_required'),
            'order_email.required' => __('webshop::product.review_validation.order_email_required'),
            'order_number.required_with' => __('webshop::product.review_validation.order_number_required_with'),
            'order_email.required_with' => __('webshop::product.review_validation.order_email_required_with'),
            'order_email.email' => __('webshop::product.review_validation.order_email_email'),
        ], [
            'order_number' => __('webshop::product.review_validation.attribute_order_number'),
            'order_email' => __('webshop::product.review_validation.attribute_order_email'),
        ]);

        $order = null;

        if ($request->filled('order_number')) {
            $order = WebshopReviewService::findPurchaseOrder(
                (int) $request->input('product_id'),
                $request->input('order_number'),
                $request->input('order_email')
            );

            if (!$order) {
                throw ValidationException::withMessages([
                    'order_number' => __('webshop::product.review_validation.order_not_found'),
                ]);
            }
        }

        $moderated = WebshopReviewService::moderationEnabled();

        WebshopProductReview::create([
            'product_id' => $request->input('product_id'),
            'name' => $request->input('name'),
            'rating' => $request->input('rating'),
            'review' => $request->input('review'),
            'is_active' => !$moderated,
            'is_verified' => $order !== null,
            'order_id' => $order?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $moderated
                ? __('webshop::product.reviews.thanks_moderated')
                : __('webshop::product.reviews.thanks'),
        ]);
    }
}
