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
            return response()->json(['success' => false, 'message' => 'A véleményezés nem engedélyezett.'], 403);
        }

        $verifiedOnly = WebshopReviewService::verifiedOnly();

        $request->validate([
            'product_id' => 'required|exists:webshop_products,id',
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
            // A vásárlás igazolása: csak-igazolt módban kötelező, egyébként opcionális,
            // de ha az egyiket megadja, a másik is kell.
            'order_number' => ($verifiedOnly ? 'required' : 'nullable|required_with:order_email') . '|string|max:64',
            'order_email' => ($verifiedOnly ? 'required' : 'nullable|required_with:order_number') . '|email|max:255',
        ], [
            // A projekt validációs nyelve nem mindig magyar – ezeket a vásárló látja
            'order_number.required' => 'Véleményt csak vásárlás után írhatsz: add meg a rendelésszámodat.',
            'order_email.required' => 'Add meg a rendeléskor használt e-mail-címedet.',
            'order_number.required_with' => 'Az e-mail-cím mellé add meg a rendelésszámot is.',
            'order_email.required_with' => 'A rendelésszám mellé add meg a rendeléskor használt e-mail-címet is.',
            'order_email.email' => 'Érvényes e-mail-címet adj meg.',
        ], ['order_number' => 'rendelésszám', 'order_email' => 'e-mail-cím']);

        $order = null;

        if ($request->filled('order_number')) {
            $order = WebshopReviewService::findPurchaseOrder(
                (int) $request->input('product_id'),
                $request->input('order_number'),
                $request->input('order_email')
            );

            if (!$order) {
                throw ValidationException::withMessages([
                    'order_number' => 'Ehhez a rendelésszámhoz és e-mail-címhez nem találtunk olyan rendelést, amelyben ez a termék szerepel.',
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
                ? 'Köszönjük a véleményét! Közzététel előtt átnézzük.'
                : 'Köszönjük a véleményét.',
        ]);
    }
}
