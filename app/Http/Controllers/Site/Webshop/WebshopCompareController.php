<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCompareService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCompareController extends Controller
{
    public function index()
    {
        if (!WebshopSettingsService::getBool('site_product_compare_enabled')) abort(404);

        $items = WebshopCompareService::getContent();
        if (count($items) == 1) {
            $firstItem = reset($items);
            $product = WebshopProduct::active()->find($firstItem['product_id']);
            if ($product && $product->category) {
                return redirect()->route('site.webshop.categories.show', $product->category->slug);
            }
        }

        if (count($items) < 2) {
            return redirect()->route('site.webshop.categories.index')->with('error', 'Legalább 2 termék szükséges az összehasonlításhoz.');
        }

        $products = WebshopProduct::active()->whereIn('id', array_keys($items))->with('productProperties.propertyCategory')->get();

        // Összes tulajdonság kategória kigyűjtése a termékekből
        $propertyCategories = [];
        foreach ($products as $product) {
            foreach ($product->productProperties as $pp) {
                $pc = $pp->propertyCategory ?? null;
                if ($pc) {
                    $propertyCategories[$pc->id] = $pc;
                }
            }
        }

        return view('site.webshop.compare.index', [
            'products' => $products,
            'propertyCategories' => $propertyCategories
        ]);
    }

    public function add(Request $request)
    {
        if (!WebshopSettingsService::getBool('site_product_compare_enabled')) {
            return response()->json(['success' => false, 'message' => 'Összehasonlítás funkció kikapcsolva.']);
        }

        $productId = $request->input('product_id');
        $result = WebshopCompareService::add($productId);

        if ($result['success']) {
            $result['count'] = WebshopCompareService::getCount();
        }

        return response()->json($result);
    }

    public function dropdown()
    {
        return response()->json([
            'success' => true,
            'html' => view('site.webshop.compare.dropdown', [
                'items' => WebshopCompareService::getContent(),
                'ws' => WebshopSettingsService::all()
            ])->render(),
            'count' => WebshopCompareService::getCount()
        ]);
    }

    public function remove(Request $request)
    {
        $productId = $request->input('product_id');
        WebshopCompareService::remove($productId);

        return response()->json([
            'success' => true,
            'count' => WebshopCompareService::getCount()
        ]);
    }
}
