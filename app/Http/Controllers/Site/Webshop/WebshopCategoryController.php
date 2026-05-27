<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductFilterService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopCategoryController extends Controller
{
    public function index()
    {
        $categories = WebshopCategory::active()->topLevel()->ordered()->get();
        return view('site.webshop.categories.index', compact('categories'));
    }

    public function show(Request $request, $categorySlug)
    {
        [$slug, $filters] = $this->parseBeautyUrl($categorySlug, $request);
        $category = WebshopCategory::where('slug', $slug)
            ->when(is_numeric($slug), function($q) use ($slug) {
                $q->orWhere('id', $slug);
            })
            ->firstOrFail();

        if (!$category->is_active) abort(404);

        $request->merge($filters);

        $children = $category->children()->active()->ordered()->get();

        if ($children->isNotEmpty()) {
            return view('site.webshop.categories.index', [
                'categories' => $children,
                'parentCategory' => $category
            ]);
        }

        return view('site.webshop.categories.show', [
            'category' => $category,
            'visibleFilterBtn' => true,
        ]);
    }

    public function products(Request $request, $categorySlug)
    {
        [$slug, $filters] = $this->parseBeautyUrl($categorySlug, $request);
        $category = WebshopCategory::where('slug', $slug)
            ->when(is_numeric($slug), function($q) use ($slug) {
                $q->orWhere('id', $slug);
            })
            ->firstOrFail();
        $request->merge($filters);

        $query = $category->products()->active();

        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $query->with('label');
        }

        $filterService = new WebshopProductFilterService();
        $query = $filterService->filter($query, $request->all());

        $perPage = $request->input('per_page', 30);
        $products = $query->paginate($perPage);

        $viewMode = $request->input('view_mode', WebshopSettingsService::get('site_product_list_default_view', 'card'));
        $ws = WebshopSettingsService::all();

        return response()->json([
            'success' => true,
            'html' => view('site.webshop.categories.partials.product-list', compact('products', 'viewMode', 'ws'))->render(),
            'pagination' => view('site.webshop.categories.partials.pagination', compact('products'))->render(),
        ]);
    }

    private function parseBeautyUrl($categorySlug, Request $request)
    {
        if (strpos($categorySlug, ';') === false) {
            return [$categorySlug, $request->all()];
        }

        $parts = explode(';', $categorySlug);
        $realSlug = array_shift($parts);
        $filters = [];

        foreach ($parts as $part) {
            if (empty($part)) continue;

            if (substr($part, 0, 1) === 'f') {
                $propId = substr($part, 1);
                $filters['f_direct'][] = $propId;
            } elseif (preg_match('/^n(\d+)(min|max)-(.*)$/', $part, $matches)) {
                $filters['n'][$matches[1]][$matches[2]] = $matches[3];
            } elseif (strpos($part, '-') !== false) {
                $parts2 = explode('-', $part, 2);
                $key = $parts2[0];
                $value = $parts2[1] ?? '';
                $filters[$key] = $value;
                if ($key === 'page') {
                    $request->merge(['page' => $value]);
                }
            }
        }

        // Visszaalakítás standard formátumra a kompatibilitás miatt
        if (isset($filters['f_direct'])) {
            $dbProperties = \Weboldalnet\WebshopAiDefault\Models\WebshopProperty::whereIn('id', $filters['f_direct'])->get();
            foreach ($dbProperties as $dbProp) {
                $filters['f'][$dbProp->property_category_id][] = (string)$dbProp->id;
            }
            unset($filters['f_direct']);
        }

        return [$realSlug, $filters];
    }
}
