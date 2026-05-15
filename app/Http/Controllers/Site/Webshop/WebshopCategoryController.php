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

    public function show(WebshopCategory $category)
    {
        if (!$category->is_active) abort(404);

        $children = $category->children()->active()->ordered()->get();

        if ($children->isNotEmpty()) {
            return view('site.webshop.categories.index', [
                'categories' => $children,
                'parentCategory' => $category
            ]);
        }

        return view('site.webshop.categories.show', [
            'category' => $category,
            'ws' => WebshopSettingsService::all()
        ]);
    }

    public function products(Request $request, WebshopCategory $category)
    {
        $query = $category->products()->active();
        
        $filterService = new WebshopProductFilterService();
        $query = $filterService->filter($query, $request->all());

        $perPage = $request->input('per_page', 30);
        $products = $query->paginate($perPage);

        $viewMode = $request->input('view_mode', 'card');
        $ws = WebshopSettingsService::all();

        return response()->json([
            'success' => true,
            'html' => view('site.webshop.categories.partials.product-list', compact('products', 'viewMode', 'ws'))->render(),
            'pagination' => view('site.webshop.categories.partials.pagination', compact('products'))->render(),
        ]);
    }
}
