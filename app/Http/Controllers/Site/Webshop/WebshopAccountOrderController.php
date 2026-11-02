<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUserService;

/**
 * Rendeléseim a vásárlói fiókban (weboldalnet/commerce-users) és az újrarendelés.
 *
 * Minden rendelés a bejelentkezett vásárló saját rendelései közül töltődik be
 * (WebshopOrder::forCommerceUser), így más rendelése azonosítóval sem nyitható meg.
 */
class WebshopAccountOrderController extends Controller
{
    public function index()
    {
        abort_unless(WebshopUserService::ordersEnabled(), 404);
        $user = WebshopUserService::user();

        $orders = WebshopOrder::forCommerceUser($user)
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('site.webshop.account.orders.index', [
            'orders' => $orders,
            'reorderEnabled' => WebshopUserService::reorderEnabled(),
        ]);
    }

    public function show(int $order)
    {
        abort_unless(WebshopUserService::ordersEnabled(), 404);

        $order = WebshopOrder::forCommerceUser(WebshopUserService::user())->with('items')->findOrFail($order);

        return view('site.webshop.account.orders.show', [
            'order' => $order,
            'reorderEnabled' => WebshopUserService::reorderEnabled(),
        ]);
    }

    public function reorder(int $order)
    {
        abort_unless(WebshopUserService::reorderEnabled(), 404);

        $order = WebshopOrder::forCommerceUser(WebshopUserService::user())->with('items')->findOrFail($order);
        $result = WebshopUserService::reorder($order);

        if (empty($result['added']) && empty($result['partial'])) {
            return back()->with('error', 'A rendelés termékei közül jelenleg egyik sem rendelhető, ezért nem került semmi a kosárba.');
        }

        $redirect = redirect()->route('site.webshop.checkout.index')
            ->with('success', 'A #' . $order->order_number . ' rendelés termékei a kosárba kerültek, a mai áron.');

        $notes = [];
        if ($result['partial']) {
            $notes[] = 'Készlethiány miatt kevesebb került a kosárba: ' . implode(', ', $result['partial']) . '.';
        }
        if ($result['unavailable']) {
            $notes[] = 'Már nem rendelhető: ' . implode(', ', $result['unavailable']) . '.';
        }

        return $notes ? $redirect->with('warning', implode(' ', $notes)) : $redirect;
    }
}
