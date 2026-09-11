<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCouponService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCartController extends Controller
{
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);
        $showRelatedModal = $request->input('show_related_modal') === '1' || $request->input('show_related_modal') === true;

        // A szolgáltatás a készlethez vágja a mennyiséget, és a ténylegesen
        // betett darabszámot adja vissza – ha kevesebb, a vásárlót tájékoztatjuk.
        $addedQuantity = WebshopCartService::add($productId, $quantity);

        $relatedWith = ['category'];
        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $relatedWith[] = 'label';
        }

        $product = WebshopProduct::with(['relatedProducts' => fn($q) => $q->active()->with($relatedWith)])->find($productId);
        $relatedHtml = '';
        if ($showRelatedModal && WebshopSettingsService::getBool('site_related_products_modal_enabled') && $product && $product->relatedProducts->isNotEmpty()) {
            $relatedHtml = view('site.webshop.modals.related-products', [
                'product' => $product,
                'relatedProductsGrouped' => $product->relatedProducts->groupBy('category_id'),
                'ws' => WebshopSettingsService::all()
            ])->render();
        }

        $message = 'A termék sikeresen kosárba került.';
        $level = 'success';

        if ($addedQuantity <= 0) {
            $message = 'Ebből a termékből jelenleg nincs több a raktáron.';
            $level = 'error';
        } elseif ($addedQuantity < $quantity) {
            $message = 'Készlethiány miatt csak ' . $addedQuantity . ' db került a kosárba.';
            $level = 'warning';
        }

        /*
           A `level` azért kell külön a `success` mellé, mert három eset van, nem
           kettő: a kérés SIKERES lehet úgy is, hogy közben a mennyiséget a
           készlethez vágtuk. Ezt a vásárlónak látnia kell, de nem hibaként.
        */
        return response()->json([
            'success' => $addedQuantity > 0,
            'level' => $level,
            'message' => $message,
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
            'related_html' => $relatedHtml,
            'has_related_products' => !empty($relatedHtml),
        ]);
    }

    public function dropdown()
    {
        return response()->json([
            'success' => true,
            'html' => view('site.webshop.cart.dropdown', [
                'items' => WebshopCartService::getContent(),
                'total' => WebshopCartService::getTotal()
            ])->render(),
            'count' => WebshopCartService::getCount()
        ]);
    }

    public function update(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity');

        // A szolgáltatás a készlethez vágja a mennyiséget, és a ténylegesen
        // beállított darabszámot adja vissza – a böngészőnek EZT kell mutatnia,
        // nem azt, amit a vásárló kért.
        $appliedQuantity = WebshopCartService::update($productId, $quantity);

        /*
           A válasz mindent visszaad, ami a pénztári összesítő JS-ből való
           frissítéséhez kell. Korábban a böngésző egyszerűen újratöltötte az
           oldalt mennyiség-változtatáskor, ami a pénztáron KITÖRÖLTE a már
           kitöltött űrlapot (név, e-mail, cím, megjegyzés, kérdőív, pipák).

           A kupon kedvezményét is ÚJRASZÁMOLJUK: az a kosár tartalmától függ,
           tehát egy mennyiség-változtatás után elavulna. Az összeget továbbra is
           kizárólag a szerver számolja, a böngésző csak megjeleníti.
        */
        $lineTotal = null;
        foreach (WebshopCartService::getContent() as $rowKey => $item) {
            if ((int) $rowKey === (int) $productId) {
                $lineTotal = (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
                break;
            }
        }

        $couponResult = WebshopCouponService::currentResult();

        return response()->json([
            'success' => true,
            'quantity' => $appliedQuantity,
            'level' => $appliedQuantity < $quantity ? 'warning' : 'success',
            'message' => $appliedQuantity < $quantity
                ? 'Készlethiány miatt csak ' . $appliedQuantity . ' db állítható be.'
                : null,
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
            'item_total' => $lineTotal,
            'coupon' => [
                'discount' => ($couponResult && $couponResult['success']) ? (float) $couponResult['discount'] : 0,
                'code' => ($couponResult && $couponResult['success']) ? $couponResult['coupon_code'] : null,
                // Ha a kosár változása miatt a kupon érvénytelenné vált, a
                // vásárlónak látnia kell, miért tűnt el a kedvezmény.
                'message' => ($couponResult && !$couponResult['success']) ? $couponResult['message'] : null,
            ],
        ]);
    }

    public function remove(Request $request)
    {
        $productId = $request->input('product_id');
        WebshopCartService::remove($productId);

        return response()->json([
            'success' => true,
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
        ]);
    }
}
