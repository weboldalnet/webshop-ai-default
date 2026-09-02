<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopWithdrawal;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * Elállási kérelmek kezelése az adminban.
 */
class WebshopWithdrawalController extends AdminExtendedController
{
    public function index(Request $request)
    {
        $query = WebshopWithdrawal::with('order')
            ->search($request->input('search'))
            ->byStatus($request->input('status'))
            ->orderBy('created_at', 'desc');

        return view('admin.webshop.withdrawals.index', [
            'withdrawals' => $query->paginate(25)->withQueryString(),
            'statusLabels' => WebshopWithdrawal::STATUSES,
        ]);
    }

    public function show(WebshopWithdrawal $withdrawal)
    {
        $withdrawal->load(['items', 'order.items']);

        return view('admin.webshop.withdrawals.show', [
            'withdrawal' => $withdrawal,
            'order' => $withdrawal->order,
            'statusLabels' => WebshopWithdrawal::STATUSES,
            'showPrices' => WebshopSettingsService::getBool('site_product_prices_visible', true),
        ]);
    }

    public function update(Request $request, WebshopWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebshopWithdrawal::STATUSES)),
            'admin_note' => 'nullable|string|max:5000',
        ]);

        $withdrawal->update($validated);

        return redirect()->route('admin.webshop.withdrawals.show', $withdrawal)
            ->with('success', 'Elállási kérelem frissítve.');
    }

    public function destroy(WebshopWithdrawal $withdrawal)
    {
        $withdrawal->items()->delete();
        $withdrawal->delete();

        return redirect()->route('admin.webshop.withdrawals.index')
            ->with('success', 'Elállási kérelem törölve.');
    }
}
