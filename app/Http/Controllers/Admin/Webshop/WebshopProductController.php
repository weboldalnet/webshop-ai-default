<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Helpers\ProductHelper;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductLabel;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductGalleryImage;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopFileService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductVariationService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebshopProductController extends AdminExtendedController
{
    public function index(Request $request)
    {
        $ws = WebshopSettingsService::all();
        $query = WebshopProduct::ordered()->with('category.children');

        if (WebshopSettingsService::getBool('admin_product_labels_enabled')) {
            $query->with('label');
        }

        if (($ws['site_product_reviews_enabled'] ?? 'false') === 'true') {
            $query->withCount('reviews');
        }
        if (($ws['product_variations_enabled'] ?? 'false') === 'true') {
            $query->withCount('variations');
        }
        if (($ws['product_related_enabled'] ?? 'false') === 'true') {
            $query->withCount('relatedProducts');
        }

        if ($request->filled('search')) $query->search($request->input('search'));
        if ($request->filled('category_id')) $query->byCategory($request->input('category_id'));
        if ($request->filled('is_active') && $request->input('is_active') !== '') $query->where('is_active', $request->input('is_active') === '1');

        return view('admin.webshop.products.index', [
            'products' => $query->get(),
            'categories' => WebshopCategory::ordered()->get(),
            'ws' => WebshopSettingsService::all(),
        ]);
    }

    public function create()
    {
        return view('admin.webshop.products.create', [
            'product' => null,
            'categories' => WebshopCategory::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'category_id' => 'required|integer']);

        $data = [
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'description' => $request->input('description'),
            'slug' => WebshopSlugService::generateUniqueSlug($request->input('name'), 'public.webshop_products'),
            'is_active' => true,
            'sort_order' => (WebshopProduct::max('sort_order') ?? 0) + 1,
        ];

        $product = WebshopProduct::create($data);

        if ($request->hasFile('primary_image')) {
            $mode = WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper');

            if ($mode !== 'cropper') {
                $category = WebshopCategory::find($data['category_id']);
                $width = $category->primary_image_width ?? config('webshop.primary_image.width', 500);
                $height = $category->primary_image_height ?? config('webshop.primary_image.height', 500);

                $imagePath = WebshopFileService::saveProductImage(
                    $request->file('primary_image'),
                    getTransformedString($data['name']) . '-' . $product->id,
                    $width,
                    $height
                );

                $thumbPath = WebshopFileService::saveProductImageThumbnail(
                    $request->file('primary_image'),
                    getTransformedString($data['name']) . '-' . $product->id,
                    config('webshop.thumbnail.width', 120),
                    config('webshop.thumbnail.height')
                );

                $product->update([
                    'primary_image' => $imagePath,
                    'primary_image_thumb' => $thumbPath,
                ]);
            }
        }

        return redirect()->route('admin.webshop.products.edit', $product)->with('success', 'Termék sikeresen létrehozva. Most már kitöltheted az összes adatot.');
    }

    public function edit(WebshopProduct $product)
    {
        $product->load('category', 'productProperties', 'galleryImages', 'relatedProducts', 'variations');
        $ws = WebshopSettingsService::all();
        $categoryPropertyCatIds = $product->category ? $product->category->propertyCategories()->pluck('webshop_property_categories.id')->toArray() : [];

        return view('admin.webshop.products.edit', [
            'product' => $product,
            'categories' => WebshopCategory::ordered()->get(),
            'ws' => $ws,
            'categoryPropertyCatIds' => $categoryPropertyCatIds,
            'allPropertyCategories' => WebshopPropertyCategory::active()->ordered()->with(['properties' => fn($q) => $q->active()->ordered()])->get(),
            'labels' => WebshopProductLabel::all(),
        ]);
    }

    public function update(Request $request, WebshopProduct $product)
    {
        $ws = WebshopSettingsService::all();
//        $rules = ['name' => 'required|string|max:255', 'category_id' => 'required|integer', 'primary_image[img]' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120'];
        $rules = ['name' => 'required|string|max:255', 'category_id' => 'required|integer'];
        if (($ws['product_price_enabled'] ?? 'false') === 'true') { $rules['price'] = 'nullable|numeric|min:0'; $rules['sale_price'] = 'nullable|numeric|min:0'; }
        if (($ws['product_stock_enabled'] ?? 'false') === 'true') { $rules['stock_quantity'] = 'nullable|integer|min:0'; }
        $request->validate($rules);

        if ($request->filled('price') && $request->filled('sale_price') && (float)$request->input('sale_price') > (float)$request->input('price')) {
            return redirect()->back()->withInput()->withErrors(['sale_price' => 'Az akciós ár nem lehet nagyobb, mint az alapár.']);
        }

        $data = $request->only(['name', 'category_id', 'description', 'sku', 'label_id']);
        $data['slug'] = WebshopSlugService::generateUniqueSlug($data['name'], 'public.webshop_products', $product->id);

        if (($ws['admin_product_labels_enabled'] ?? 'false') !== 'true') {
            unset($data['label_id']);
        }

        if (($ws['product_price_enabled'] ?? 'false') === 'true') { $data['price'] = $request->input('price'); $data['sale_price'] = $request->input('sale_price'); }
        if (($ws['product_stock_enabled'] ?? 'false') === 'true') { $data['stock_enabled'] = $request->has('stock_enabled'); $data['stock_quantity'] = $request->input('stock_quantity'); }
        if ($request->hasFile('primary_image')) {
            $mode = WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper');

            $imageFile = $mode === 'cropper' ? $request->file('primary_image')['img'] : $request->file('primary_image');

            if ($mode === 'cropper') {
                $sizes = $request->input('primary_image');
                $width = $sizes['width'];
                $height = $sizes['height'];
            } else {
                $category = WebshopCategory::find($data['category_id']);
                $width = $category->primary_image_width ?? config('webshop.primary_image.width', 500);
                $height = $category->primary_image_height ?? config('webshop.primary_image.height', 500);
            }

            $data['primary_image'] = WebshopFileService::saveProductImage(
                $imageFile,
                getTransformedString($data['name']).'-'.$product->id,
                $width,
                $height
            );

            $data['primary_image_thumb'] = WebshopFileService::saveProductImageThumbnail(
                $imageFile,
                getTransformedString($data['name']).'-'.$product->id,
                config('webshop.thumbnail.width', 120),
                config('webshop.thumbnail.height')
            );
        }

        $product->update($data);
        $this->saveProductProperties($product, $request);

        if (($ws['product_related_enabled'] ?? 'false') === 'true') {
            $relatedIds = array_unique(array_filter($request->input('related_product_ids', []), fn($id) => (int)$id !== $product->id && (int)$id > 0));
            $product->relatedProducts()->sync($relatedIds);
        }
        if (($ws['product_variations_enabled'] ?? 'false') === 'true') {
            $variationIds = array_unique(array_filter($request->input('variation_product_ids', []), fn($id) => (int)$id !== $product->id && (int)$id > 0));
            WebshopProductVariationService::syncVariations($product, $variationIds);
        }

        return redirect()->route('admin.webshop.products.edit', $product)->with('success', 'Termék sikeresen frissítve.');
    }

    public function destroy(WebshopProduct $product)
    {
        WebshopProductVariationService::removeProductFromVariations($product);
        $product->delete();
        return redirect()->route('admin.webshop.products.index')->with('success', 'Termék sikeresen törölve.');
    }

    public function toggleActive(Request $request)
    {
        $p = WebshopProduct::findOrFail($request->input('id'));
        $p->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $p->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }

    public function sort(Request $request)
    {
        $order = 1;
        foreach ($request->input('orderedIds', []) as $id) { WebshopProduct::where('id', $id)->update(['sort_order' => $order++]); }
        return response()->json(['success' => true, 'message' => 'Sorrend mentve.']);
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $excludeId = $request->input('exclude_id');
        $isVariation = (int)$request->input('is_variation');

        $query = WebshopProduct::active();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%' . $search . '%')
                  ->orWhere('sku', 'ILIKE', '%' . $search . '%');
            });
        }

        if ($isVariation === 1 && $excludeId) {
            $product = WebshopProduct::find($excludeId);
            if ($product) {
                // Csak azonos kategóriában lévő termékek
                $query->where('category_id', $product->category_id);

                // Az adott terméket ne dobja fel
                $query->where('id', '!=', $excludeId);

                // Olyan terméket ne dobjon fel, ami variációja már egy másik terméknek
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('webshop_product_variations')
                        ->where(function ($sub) {
                            $sub->whereRaw('webshop_product_variations.product_id = webshop_products.id')
                                ->orWhereRaw('webshop_product_variations.variation_product_id = webshop_products.id');
                        });
                });
            }
        } elseif ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $products = $query->with('category')
            ->limit(20)
            ->get();

        return response()->json($products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'category_name' => $p->category->name_singular ?? '',
            'primary_image' => $p->primary_image_thumb ?? $p->primary_image,
        ]));
    }

    public function storeGalleryImage(Request $request, WebshopProduct $product)
    {
        $request->validate(['gallery_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', 'alt' => 'nullable|string|max:255']);
        $image = WebshopFileService::saveGalleryImage($request->file('gallery_image'), getTransformedString($product->name).'-gallery-'.$product->id);
        $imageThumb = WebshopFileService::saveGalleryImageThumbnail($request->file('gallery_image'), getTransformedString($product->name).'-gallery-thumb-'.$product->id);
        $galleryItem = WebshopProductGalleryImage::create([
            'product_id' => $product->id,
            'image' => $image,
            'image_thumb' => $imageThumb,
            'alt' => $request->input('alt'),
            'sort_order' => ($product->galleryImages()->max('sort_order') ?? 0) + 1,
            'is_active' => true
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Galéria kép sikeresen feltöltve.',
                'html' => view('admin.webshop.products.partials.gallery-item', [
                    'product' => $product,
                    'img' => $galleryItem
                ])->render()
            ]);
        }

        return redirect()->route('admin.webshop.products.edit', $product)->with('success', 'Galéria kép sikeresen feltöltve.');
    }

    public function destroyGalleryImage(WebshopProduct $product, WebshopProductGalleryImage $image)
    {
        $image->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Galéria kép sikeresen törölve.']);
        }
        return redirect()->route('admin.webshop.products.edit', $product)->with('success', 'Galéria kép sikeresen törölve.');
    }

    public function sortGallery(Request $request)
    {
        $order = 1;
        foreach ($request->input('orderedIds', []) as $id) { WebshopProductGalleryImage::where('id', $id)->update(['sort_order' => $order++]); }
        return response()->json(['success' => true, 'message' => 'Galéria sorrend mentve.']);
    }

    public function toggleGalleryActive(Request $request)
    {
        $img = WebshopProductGalleryImage::findOrFail($request->input('id'));
        $img->is_active = $request->input('is_active') === 'true' || $request->input('is_active') === true;
        $img->save();
        return response()->json(['success' => true, 'message' => 'Státusz frissítve.']);
    }

    public function updateGalleryAlt(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'alt' => 'nullable|string|max:255']);
        $img = WebshopProductGalleryImage::findOrFail($request->input('id'));
        $img->alt = $request->input('alt');
        $img->save();
        return response()->json(['success' => true, 'message' => 'Alt szöveg frissítve.']);
    }

    private function saveProductProperties(WebshopProduct $product, Request $request): void
    {
        $product->productProperties()->delete();
        foreach ($request->input('properties', []) as $pcId => $values) {
            $pc = WebshopPropertyCategory::find($pcId);
            if (!$pc) continue;
            if ($pc->isNumber()) {
                if (isset($values['number_value']) && $values['number_value'] !== '' && $values['number_value'] !== null) {
                    WebshopProductProperty::create(['product_id' => $product->id, 'property_category_id' => $pcId, 'number_value' => $values['number_value']]);
                }
            } else {
                $selected = $values['selected'] ?? [];
                if (!is_array($selected)) $selected = [$selected];
                foreach ($selected as $propId) {
                    if ($propId) WebshopProductProperty::create(['product_id' => $product->id, 'property_category_id' => $pcId, 'property_id' => $propId]);
                }
            }
        }
    }
}
