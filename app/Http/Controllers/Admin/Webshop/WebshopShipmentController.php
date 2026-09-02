<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

/**
 * Szállítmányok listája – provider-független.
 *
 * A commerce_shipments táblát jeleníti meg, tehát bármelyik szállítási
 * provider (GLS, később más futárszolgálat) szállítmányai itt látszanak.
 */
class WebshopShipmentController extends AdminExtendedController
{
    public function index(Request $request)
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Models\Shipment::class)) {
            return redirect()->route('admin.webshop.orders.index')
                ->with('error', 'A commerce-core csomag nincs telepítve.');
        }

        $query = \Weboldalnet\CommerceCore\Models\Shipment::with('order')
            ->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'ILIKE', '%' . $search . '%')
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'ILIKE', '%' . $search . '%')
                            ->orWhere('customer_name', 'ILIKE', '%' . $search . '%');
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        return view('admin.webshop.shipments.index', [
            'shipments' => $query->paginate(20)->withQueryString(),
            'shippingLabels' => WebshopCommerceService::getAllShippingMethodLabels(),
            'statusLabels' => WebshopCommerceService::getAllShippingStatusLabels(),
        ]);
    }

    /**
     * Címke letöltése. A fájl a privát tárhelyen van, csak hitelesített
     * adminon keresztül érhető el.
     */
    public function downloadLabel($id)
    {
        $shipment = \Weboldalnet\CommerceCore\Models\Shipment::findOrFail($id);

        if (!$shipment->label_path) {
            return redirect()->back()->with('error', 'Ehhez a szállítmányhoz nincs eltárolt címke.');
        }

        if (!Storage::disk('local')->exists($shipment->label_path)) {
            Log::warning('Szállítmány címke hiányzik a tárhelyről.', [
                'shipment_id' => $shipment->id,
                'label_path' => $shipment->label_path,
            ]);

            return redirect()->back()->with('error', 'A címke fájl nem található a szerveren.');
        }

        $name = ($shipment->tracking_number ?: ('szallitmany-' . $shipment->id)) . '.pdf';

        return Storage::disk('local')->download($shipment->label_path, $name);
    }

    /**
     * Szállítmány létrehozása (újrapróbálás) egy rendeléshez az admin listából.
     */
    public function retry($id)
    {
        $shipment = \Weboldalnet\CommerceCore\Models\Shipment::findOrFail($id);
        $order = WebshopOrder::find($shipment->order_id);

        if (!$order) {
            return redirect()->back()->with('error', 'A szállítmányhoz nem található rendelés.');
        }

        $result = WebshopCommerceService::createShipment($order);

        if (!empty($result['success'])) {
            return redirect()->back()->with('success', 'Szállítmány sikeresen létrehozva.');
        }

        return redirect()->back()->with('error', 'Sikertelen: ' . ($result['message'] ?? 'ismeretlen hiba'));
    }
}
