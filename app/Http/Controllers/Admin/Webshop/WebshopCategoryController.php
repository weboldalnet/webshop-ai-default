<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopFileService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Illuminate\Http\Request;

class WebshopCategoryController extends AdminExtendedController
{
    public function index(Request $request)
    {
        $query = WebshopCategory::ordered()->with('parent');
        if ($request->filled('search')) $query->search($request->input('search'));
        if ($request->filled('is_active') && $request->input('is_active') !== '') $query->where('is_active', $request->input('is_active') === '1');

        return view('admin.webshop.categories.index', ['categories' => $query->get()]);
    }

    public function create()
    {
        return view('admin.webshop.categories.create', [
            'category' => null,
            'allCategories' => WebshopCategory::ordered()->get(),
            'propertyCategories' => WebshopPropertyCategory::active()->ordered()->get(),
            'ws' => WebshopSettingsService::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_singular' => 'required|string|max:255',
            'name_plural' => 'required|string|max:255',
            'og_img' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'icon_file' => 'nullable|file|mimes:svg,png|max:2048',
        ]);

        $data = $request->only(['name_singular', 'name_plural', 'description', 'og_title', 'og_description', 'parent_id']);
        $data['slug'] = WebshopSlugService::generateUniqueSlug($data['name_singular'], 'public.webshop_categories');
        $data['is_active'] = true;
        $data['sort_order'] = (WebshopCategory::max('sort_order') ?? 0) + 1;
        if (empty($data['og_title'])) $data['og_title'] = $data['name_singular'];
        if (empty($data['og_description'])) $data['og_description'] = $data['description'] ?? '';
        if (!WebshopSettingsService::getBool('category_parent_enabled')) $data['parent_id'] = null;

        if ($request->hasFile('og_img')) {
            $data['og_img'] = WebshopFileService::saveCategoryOgImage($request->file('og_img'), getTransformedString($data['name_singular']));
        }
        if ($request->hasFile('icon_file') && WebshopSettingsService::getBool('category_icon_enabled')) {
            $data['icon'] = WebshopFileService::saveCategoryIcon($request->file('icon_file'), getTransformedString($data['name_singular']));
        }

        $category = WebshopCategory::create($data);
        $this->syncPropertyCategories($category, $request);

        if (WebshopSettingsService::getBool('category_related_enabled')) {
            $relatedIds = array_filter($request->input('related_categories', []), fn($id) => (int)$id !== $category->id);
            $category->relatedCategories()->sync($relatedIds);
        }

        return redirect()->route('admin.webshop.categories.index')->with('success', 'Kategória sikeresen létrehozva.');
    }

    public function edit(WebshopCategory $category)
    {
        $category->load('propertyCategories', 'relatedCategories');
        return view('admin.webshop.categories.edit', [
            'category' => $category,
            'allCategories' => WebshopCategory::ordered()->where('id', '!=', $category->id)->get(),
            'propertyCategories' => WebshopPropertyCategory::active()->ordered()->get(),
            'ws' => WebshopSettingsService::all(),
        ]);
    }

    public function update(Request $request, WebshopCategory $category)
    {
        $request->validate([
            'name_singular' => 'required|string|max:255',
            'name_plural' => 'required|string|max:255',
            'og_img' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'icon_file' => 'nullable|file|mimes:svg,png|max:2048',
        ]);

        $data = $request->only(['name_singular', 'name_plural', 'description', 'og_title', 'og_description', 'parent_id']);
        $data['slug'] = WebshopSlugService::generateUniqueSlug($data['name_singular'], 'public.webshop_categories', $category->id);
        if (empty($data['og_title'])) $data['og_title'] = $data['name_singular'];
        if (empty($data['og_description'])) $data['og_description'] = $data['description'] ?? '';
        if (!WebshopSettingsService::getBool('category_parent_enabled')) $data['parent_id'] = null;

        if ($request->hasFile('og_img')) {
            $data['og_img'] = WebshopFileService::saveCategoryOgImage($request->file('og_img'), getTransformedString($data['name_singular']));
        }
        if ($request->hasFile('icon_file') && WebshopSettingsService::getBool('category_icon_enabled')) {
            $data['icon'] = WebshopFileService::saveCategoryIcon($request->file('icon_file'), getTransformedString($data['name_singular']));
        }

        $category->update($data);
        $this->syncPropertyCategories($category, $request);

        if (WebshopSettingsService::getBool('category_related_enabled')) {
            $relatedIds = array_filter($request->input('related_categories', []), fn($id) => (int)$id !== $category->id);
            $category->relatedCategories()->sync($relatedIds);
        }

        return redirect()->route('admin.webshop.categories.index')->with('success', 'Kategória sikeresen frissítve.');
    }

    public function destroy(WebshopCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.webshop.categories.index')->with('success', 'Kategória sikeresen törölve.');
    }

    public function toggleActive(Request $request)
    {
        $c = WebshopCategory::findOrFail($request->input('id'));
        $c->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $c->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }

    public function sort(Request $request)
    {
        $order = 1;
        foreach ($request->input('orderedIds', []) as $id) {
            WebshopCategory::where('id', $id)->update(['sort_order' => $order++]);
        }
        return response()->json(['success' => true, 'message' => 'Sorrend mentve.']);
    }

    private function syncPropertyCategories(WebshopCategory $category, Request $request): void
    {
        $propCatIds = $request->input('property_categories', []);
        $showOnCard = $request->input('show_on_product_card', []);
        $syncData = [];
        $order = 1;
        foreach ($propCatIds as $pcId) {
            $syncData[$pcId] = ['show_on_product_card' => in_array($pcId, $showOnCard), 'sort_order' => $order++];
        }
        $category->propertyCategories()->sync($syncData);
    }
}
