<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tulajdonságok egy tulajdonságcsoporton belül.
 *
 * A lista oldal AJAX-os (2026-10-28): a keresés, a felvétel, a szerkesztés és a törlés JSON-t ad, ha a kérés
 * AJAX; a régi, oldalas útvonalak (create/edit és a sima űrlap-POST) tartaléknak megmaradnak.
 */
class WebshopPropertyController extends AdminExtendedController
{
    public function index(Request $request, WebshopPropertyCategory $propertyCategory)
    {
        if ($propertyCategory->isNumber()) {
            return redirect()->route('admin.webshop.property-categories.index')
                ->with('error', 'Number típusú tulajdonság kategóriához nem tartozhatnak tulajdonságok.');
        }

        $search = trim((string) $request->input('search', ''));
        $properties = $propertyCategory->properties()->ordered()->search($search)->get();

        $data = [
            'propertyCategory' => $propertyCategory,
            'properties' => $properties,
            'search' => $search,
            // hány termékhez van hozzárendelve: törlés előtt ebből látszik, mit érint
            'usage' => WebshopProductProperty::query()
                ->whereIn('property_id', $properties->pluck('id'))
                ->selectRaw('property_id, count(distinct product_id) as product_count')
                ->groupBy('property_id')
                ->pluck('product_count', 'property_id'),
            'total' => $propertyCategory->properties()->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.webshop.properties.partials.rows', $data)->render(),
                'count' => $properties->count(),
                'total' => $data['total'],
            ]);
        }

        return view('admin.webshop.properties.index', $data);
    }

    public function create(WebshopPropertyCategory $propertyCategory)
    {
        if ($propertyCategory->isNumber()) {
            return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('error', 'Number típusú kategóriához nem lehet tulajdonságot létrehozni.');
        }
        return view('admin.webshop.properties.create', ['propertyCategory' => $propertyCategory, 'property' => null]);
    }

    public function store(Request $request, WebshopPropertyCategory $propertyCategory)
    {
        if ($propertyCategory->isNumber()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Szám típusú csoporthoz nem lehet tulajdonságot létrehozni.'], 422);
            }
            return redirect()->route('admin.webshop.property-categories.index')->with('error', 'Number típusú kategóriához nem lehet tulajdonságot létrehozni.');
        }
        $request->validate($this->rules($propertyCategory), $this->messages());

        $property = WebshopProperty::create([
            'property_category_id' => $propertyCategory->id,
            'name' => trim($request->input('name')),
            'slug' => WebshopSlugService::generateUniqueSlug($request->input('name'), 'public.webshop_properties'),
            'is_active' => true,
            'sort_order' => ($propertyCategory->properties()->max('sort_order') ?? 0) + 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tulajdonság létrehozva: ' . $property->name,
                'property' => ['id' => $property->id, 'name' => $property->name],
            ]);
        }
        return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('success', 'Tulajdonság sikeresen létrehozva.');
    }

    public function edit(WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        return view('admin.webshop.properties.edit', compact('propertyCategory', 'property'));
    }

    public function update(Request $request, WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        $request->validate($this->rules($propertyCategory, $property), $this->messages());
        $property->update([
            'name' => trim($request->input('name')),
            'slug' => WebshopSlugService::generateUniqueSlug($request->input('name'), 'public.webshop_properties', $property->id),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tulajdonság frissítve: ' . $property->name,
                'property' => ['id' => $property->id, 'name' => $property->name],
            ]);
        }
        return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('success', 'Tulajdonság sikeresen frissítve.');
    }

    public function destroy(Request $request, WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        $property->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tulajdonság törölve: ' . $property->name]);
        }
        return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('success', 'Tulajdonság sikeresen törölve.');
    }

    public function toggleActive(Request $request)
    {
        $p = WebshopProperty::findOrFail($request->input('id'));
        $p->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $p->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }

    public function sort(Request $request)
    {
        $order = 1;
        foreach ($request->input('orderedIds', []) as $id) {
            WebshopProperty::where('id', $id)->update(['sort_order' => $order++]);
        }
        return response()->json(['success' => true, 'message' => 'Sorrend mentve.']);
    }

    /**
     * Egy csoporton belül két azonos nevű tulajdonság a termékszerkesztőben és a szűrőben
     * megkülönböztethetetlen lenne — a gyors, „Mentés és új” felvitelnél könnyű duplán rögzíteni.
     */
    private function rules(WebshopPropertyCategory $propertyCategory, ?WebshopProperty $property = null): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                // modell-osztállyal: a "public.webshop_properties" alakot a validátor kapcsolatnévnek venné
                Rule::unique(WebshopProperty::class, 'name')
                    ->where('property_category_id', $propertyCategory->id)
                    ->whereNull('deleted_at')
                    ->ignore($property?->id),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Add meg a tulajdonság nevét.',
            'name.unique' => 'Ilyen nevű tulajdonság már van ebben a csoportban.',
        ];
    }
}
