<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Illuminate\Http\Request;

class WebshopPropertyCategoryController extends AdminExtendedController
{
    public function index(Request $request)
    {
        $query = WebshopPropertyCategory::ordered();
        if ($request->filled('search')) $query->search($request->input('search'));
        if ($request->filled('is_active') && $request->input('is_active') !== '') $query->where('is_active', $request->input('is_active') === '1');
        if ($request->filled('filter_enabled') && $request->input('filter_enabled') !== '') $query->where('filter_enabled', $request->input('filter_enabled') === '1');
        if ($request->filled('filter_type')) $query->where('filter_type', $request->input('filter_type'));

        return view('admin.webshop.property-categories.index', ['propertyCategories' => $query->get()]);
    }

    public function create()
    {
        return view('admin.webshop.property-categories.create', ['propertyCategory' => null]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'filter_type' => 'required|in:checkbox,radio,number']);

        $data = $request->only(['name', 'filter_type', 'suffix']);
        $data['slug'] = WebshopSlugService::generateUniqueSlug($data['name'], 'public.webshop_property_categories');
        $data['filter_enabled'] = $request->has('filter_enabled');
        $data['is_active'] = true;
        $data['sort_order'] = (WebshopPropertyCategory::max('sort_order') ?? 0) + 1;
        if ($data['filter_type'] !== 'number') $data['suffix'] = null;

        WebshopPropertyCategory::create($data);
        return redirect()->route('admin.webshop.property-categories.index')->with('success', 'Tulajdonság kategória sikeresen létrehozva.');
    }

    public function edit(WebshopPropertyCategory $propertyCategory)
    {
        return view('admin.webshop.property-categories.edit', compact('propertyCategory'));
    }

    public function update(Request $request, WebshopPropertyCategory $propertyCategory)
    {
        $request->validate(['name' => 'required|string|max:255', 'filter_type' => 'required|in:checkbox,radio,number']);

        $data = $request->only(['name', 'filter_type', 'suffix']);
        $data['slug'] = WebshopSlugService::generateUniqueSlug($data['name'], 'public.webshop_property_categories', $propertyCategory->id);
        $data['filter_enabled'] = $request->has('filter_enabled');
        if ($data['filter_type'] !== 'number') $data['suffix'] = null;

        $propertyCategory->update($data);
        return redirect()->route('admin.webshop.property-categories.index')->with('success', 'Tulajdonság kategória sikeresen frissítve.');
    }

    public function destroy(WebshopPropertyCategory $propertyCategory)
    {
        $propertyCategory->delete();
        return redirect()->route('admin.webshop.property-categories.index')->with('success', 'Tulajdonság kategória sikeresen törölve.');
    }

    public function toggleActive(Request $request)
    {
        $pc = WebshopPropertyCategory::findOrFail($request->input('id'));
        $pc->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $pc->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }

    public function sort(Request $request)
    {
        $order = 1;
        foreach ($request->input('orderedIds', []) as $id) {
            WebshopPropertyCategory::where('id', $id)->update(['sort_order' => $order++]);
        }
        return response()->json(['success' => true, 'message' => 'Sorrend mentve.']);
    }
}
