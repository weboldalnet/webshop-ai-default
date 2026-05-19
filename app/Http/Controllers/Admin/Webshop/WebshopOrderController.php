<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebshopOrderController extends AdminExtendedController
{
    public function create()
    {
        $products = WebshopProduct::active()->orderBy('name')->get();
        $pricesVisible = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('site_product_prices_visible', true);
        return view('admin.webshop.orders.create', compact('products', 'pricesVisible'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebshopOrder::STATUSES)),
            'type' => 'required|in:' . implode(',', array_keys(WebshopOrder::TYPES)),
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_company' => 'nullable|string|max:255',
            'customer_tax_number' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:public.webshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($request->input('items') as $itemData) {
                $product = WebshopProduct::findOrFail($itemData['product_id']);
                $price = $product->sale_price ?? $product->price ?? 0;
                $quantity = (int)$itemData['quantity'];
                $lineTotal = $price * $quantity;
                $totalPrice += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $lineTotal,
                ];
            }

            $orderNumber = $this->generateOrderNumber();
            $isCompleted = $request->has('is_completed') || $request->input('status') === WebshopOrder::STATUS_COMPLETED;

            $order = WebshopOrder::create([
                'order_number' => $orderNumber,
                'status' => $request->input('status'),
                'type' => $request->input('type'),
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_company' => $request->input('customer_company'),
                'customer_tax_number' => $request->input('customer_tax_number'),
                'billing_data' => $request->has('billing') ? json_encode($request->input('billing')) : null,
                'shipping_data' => $request->has('shipping') ? json_encode($request->input('shipping')) : null,
                'total_price' => $totalPrice,
                'currency' => 'HUF',
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'admin_note' => $request->input('admin_note'),
                'note' => $request->input('note'),
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                WebshopOrderItem::create($itemData);
            }

            return redirect()->route('admin.webshop.orders.index')->with('success', 'Rendelés sikeresen létrehozva.');
        });
    }

    private function generateOrderNumber(): string
    {
        $prefix = date('Ymd');
        $random = strtoupper(Str::random(4));
        $number = $prefix . '-' . $random;

        while (WebshopOrder::where('order_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = $prefix . '-' . $random;
        }

        return $number;
    }

    public function index(Request $request)
    {
        $query = WebshopOrder::orderBy('created_at', 'desc');
        if ($request->filled('search')) $query->search($request->input('search'));
        if ($request->filled('status')) $query->byStatus($request->input('status'));
        if ($request->filled('is_completed') && $request->input('is_completed') !== '') $query->completed($request->input('is_completed'));
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');

        $pricesVisible = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('site_product_prices_visible', true);

        return view('admin.webshop.orders.index', [
            'orders' => $query->get(),
            'pricesVisible' => $pricesVisible
        ]);
    }

    public function details(WebshopOrder $order)
    {
        $order->load('items');
        $pricesVisible = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('site_product_prices_visible', true);
        
        $billingData = $order->billing_data ? json_decode($order->billing_data, true) : null;
        $shippingData = $order->shipping_data ? json_decode($order->shipping_data, true) : null;

        $html = view('admin.webshop.orders.partials.order-details-content', [
            'order' => $order,
            'pricesVisible' => $pricesVisible,
            'billingData' => $billingData,
            'shippingData' => $shippingData,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function edit(WebshopOrder $order)
    {
        $order->load('items');
        $pricesVisible = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('site_product_prices_visible', true);
        $billingData = $order->billing_data ? json_decode($order->billing_data, true) : null;
        $shippingData = $order->shipping_data ? json_decode($order->shipping_data, true) : null;

        return view('admin.webshop.orders.edit', compact('order', 'pricesVisible', 'billingData', 'shippingData'));
    }

    public function update(Request $request, WebshopOrder $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebshopOrder::STATUSES)),
            'type' => 'required|in:' . implode(',', array_keys(WebshopOrder::TYPES)),
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_company' => 'nullable|string|max:255',
            'customer_tax_number' => 'nullable|string|max:20',
            'admin_note' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = $request->only([
            'status', 'type', 'customer_name', 'customer_email', 'customer_phone', 
            'customer_company', 'customer_tax_number', 'admin_note', 'note'
        ]);

        // Számlázási és szállítási adatok
        if ($request->has('billing')) {
            $data['billing_data'] = json_encode($request->input('billing'));
        }
        if ($request->has('shipping')) {
            $data['shipping_data'] = json_encode($request->input('shipping'));
        }

        $isCompleted = $request->has('is_completed') || $request->input('status') === WebshopOrder::STATUS_COMPLETED;
        $data['is_completed'] = $isCompleted;
        
        if ($isCompleted && !$order->is_completed) {
            $data['completed_at'] = now();
        } elseif (!$isCompleted) {
            $data['completed_at'] = null;
        }

        $order->update($data);
        return redirect()->route('admin.webshop.orders.edit', $order)->with('success', 'Rendelés sikeresen frissítve.');
    }

    public function destroy(WebshopOrder $order)
    {
        $order->delete();
        return redirect()->route('admin.webshop.orders.index')->with('success', 'Rendelés sikeresen törölve.');
    }

    public function updateStatus(Request $request, WebshopOrder $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(WebshopOrder::STATUSES)),
            'admin_note' => 'nullable|string'
        ]);

        $data = $request->only(['status', 'admin_note']);
        
        // Ha a státusz completed, akkor legyen is_completed = true, egyébként false
        if ($data['status'] === WebshopOrder::STATUS_COMPLETED) {
            $data['is_completed'] = true;
            if (!$order->is_completed) {
                $data['completed_at'] = now();
            }
        } else {
            $data['is_completed'] = false;
            $data['completed_at'] = null;
        }

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Rendelés státusza sikeresen módosítva.'
        ]);
    }

    public function toggleCompleted(Request $request)
    {
        $order = WebshopOrder::findOrFail($request->input('id'));
        $isCompleted = $request->input('is_completed') === 'true' || $request->input('is_completed') === true;
        
        $order->is_completed = $isCompleted;
        $order->completed_at = $isCompleted ? now() : null;
        
        // Státusz frissítése is
        if ($isCompleted) {
            $order->status = WebshopOrder::STATUS_COMPLETED;
        } else {
            // Ha visszavonják, legyen feldolgozás alatt
            $order->status = WebshopOrder::STATUS_PROCESSING;
        }
        
        $order->save();
        return response()->json(['success' => true, 'message' => 'Rendelés státusza és teljesítése frissítve.']);
    }
}
