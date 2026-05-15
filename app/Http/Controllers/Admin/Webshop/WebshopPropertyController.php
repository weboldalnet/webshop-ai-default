<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Illuminate\Http\Request;

class WebshopPropertyController extends AdminExtendedController
{
    public function index(Request $request, WebshopPropertyCategory $propertyCategory)
    {
        if ($propertyCategory->isNumber()) {
            return redirect()->route('admin.webshop.property-categories.index')
                ->with('error', 'Number típusú tulajdonság kategóriához nem tartozhatnak tulajdonságok.');
        }
        $query = $propertyCategory->properties()->ordered();
        if ($request->filled('search')) $query->where('name', 'ILIKE', '%'.$request->input('search').'%');

        return view('admin.webshop.properties.index', ['propertyCategory' => $propertyCategory, 'properties' => $query->get()]);
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
            return redirect()->route('admin.webshop.property-categories.index')->with('error', 'Number típusú kategóriához nem lehet tulajdonságot létrehozni.');
        }
        $request->validate(['name' => 'required|string|max:255']);

        WebshopProperty::create([
            'property_category_id' => $propertyCategory->id,
            'name' => $request->input('name'),
            'slug' => WebshopSlugService::generateUniqueSlug($request->input('name'), 'public.webshop_properties'),
            'is_active' => true,
            'sort_order' => ($propertyCategory->properties()->max('sort_order') ?? 0) + 1,
        ]);
        return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('success', 'Tulajdonság sikeresen létrehozva.');
    }

    public function edit(WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        return view('admin.webshop.properties.edit', compact('propertyCategory', 'property'));
    }

    public function update(Request $request, WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $property->update([
            'name' => $request->input('name'),
            'slug' => WebshopSlugService::generateUniqueSlug($request->input('name'), 'public.webshop_properties', $property->id),
        ]);
        return redirect()->route('admin.webshop.properties.index', $propertyCategory)->with('success', 'Tulajdonság sikeresen frissítve.');
    }

    public function destroy(WebshopPropertyCategory $propertyCategory, WebshopProperty $property)
    {
        $property->delete();
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
}
