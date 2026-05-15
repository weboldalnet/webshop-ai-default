<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Illuminate\Http\Request;

class WebshopOrderController extends AdminExtendedController
{
    public function index(Request $request)
    {
        $query = WebshopOrder::orderBy('created_at', 'desc');
        if ($request->filled('search')) $query->search($request->input('search'));
        if ($request->filled('status')) $query->byStatus($request->input('status'));
        if ($request->filled('is_completed') && $request->input('is_completed') !== '') $query->completed($request->input('is_completed'));
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');

        return view('admin.webshop.orders.index', ['orders' => $query->get()]);
    }

    public function edit(WebshopOrder $order)
    {
        $order->load('items');
        return view('admin.webshop.orders.edit', compact('order'));
    }

    public function update(Request $request, WebshopOrder $order)
    {
        $request->validate(['status' => 'required|in:' . implode(',', array_keys(WebshopOrder::STATUSES)), 'admin_note' => 'nullable|string']);

        $data = $request->only(['status', 'admin_note']);
        $isCompleted = $request->has('is_completed');
        $data['is_completed'] = $isCompleted;
        if ($isCompleted && !$order->is_completed) $data['completed_at'] = now();
        elseif (!$isCompleted) $data['completed_at'] = null;

        $order->update($data);
        return redirect()->route('admin.webshop.orders.edit', $order)->with('success', 'Rendelés sikeresen frissítve.');
    }

    public function destroy(WebshopOrder $order)
    {
        $order->delete();
        return redirect()->route('admin.webshop.orders.index')->with('success', 'Rendelés sikeresen törölve.');
    }

    public function toggleCompleted(Request $request)
    {
        $order = WebshopOrder::findOrFail($request->input('id'));
        $isCompleted = $request->input('is_completed') === 'true' || $request->input('is_completed') === true;
        $order->is_completed = $isCompleted;
        $order->completed_at = $isCompleted ? now() : null;
        $order->save();
        return response()->json(['success' => true, 'message' => 'Teljesítési státusz frissítve.']);
    }
}
