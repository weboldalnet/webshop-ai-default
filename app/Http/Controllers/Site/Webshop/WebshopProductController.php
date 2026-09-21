<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopProductController extends Controller
{
    /** A találati lista ennyi terméket mutat; efölött csak a darabszámot írjuk ki */
    const SEARCH_LIMIT = 5;

    /** Ennél rövidebb kifejezésre nem keresünk (a kliens is ezt figyeli) */
    const SEARCH_MIN_LENGTH = 3;

    /**
     * Szabadszavas termékkeresés a fejléc keresőjéhez.
     *
     * RENDERELT HTML-t ad vissza, nem JSON-t: a találati lista ugyanazokat a
     * blade-részleteket használhatja, mint az oldal többi része, és a kliensnek
     * nem kell markupot építenie.
     *
     * A kapcsoló kikapcsolt állapotában 404: enélkül a végpont akkor is
     * kereshető maradna, ha a funkció ki van kapcsolva.
     */
    public function search(Request $request)
    {
        if (!WebshopSettingsService::getBool('site_header_search_enabled')) {
            abort(404);
        }

        $searchTerm = trim((string) $request->input('q', ''));

        if (mb_strlen($searchTerm) < self::SEARCH_MIN_LENGTH) {
            return response('');
        }

        // ILIKE: a Postgres kis-nagybetű független illesztése.
        // A minta-karaktereket escape-eljük, különben a beírt % vagy _ jel
        // mindenre illeszkedne (a % "bármi", a _ "egy tetszőleges karakter").
        $escapeCharacters = '%_' . chr(92);
        $pattern = '%' . addcslashes($searchTerm, $escapeCharacters) . '%';

        $query = WebshopProduct::active()
            ->with('category')
            ->where(function ($q) use ($pattern) {
                $q->where('name', 'ilike', $pattern)
                    ->orWhere('secondary_name', 'ilike', $pattern)
                    ->orWhere('sku', 'ilike', $pattern)
                    ->orWhere('short_desc', 'ilike', $pattern);
            });

        $totalCount = (clone $query)->count();

        $products = $query->orderBy('sort_order')->orderBy('id')
            ->limit(self::SEARCH_LIMIT)
            ->get();

        return view('site.webshop.partials.search-results', [
            'searchValue' => $searchTerm,
            'products' => $products,
            'resultCount' => $totalCount,
        ])->render();
    }
    public function show(WebshopProduct $product)
    {
        if (!$product->is_active) abort(404);

        /*
           A tulajdonságokat SZŰRVE töltjük be. Enélkül egy adminban inaktivált
           tulajdonság (vagy tulajdonság-kategória) továbbra is megjelent az éles
           termékoldalon: a mentett kapcsolósor ugyanis megmarad, csak a választható
           listából tűnik el. A vásárló így nem létező jellemzőt látott.
        */
        $relations = [
            'category',
            'relatedProducts',
            'variations',
            'productProperties' => function ($query) {
                $query->whereHas('propertyCategory', fn($q) => $q->where('is_active', true))
                    ->where(function ($q) {
                        // Vagy egy AKTÍV tulajdonságra mutat, vagy szám értéket hordoz
                        $q->whereHas('property', fn($p) => $p->where('is_active', true))
                            ->orWhereNull('property_id');
                    });
            },
            'productProperties.property' => fn($query) => $query->where('is_active', true),
            'productProperties.propertyCategory',
        ];
        
        if (WebshopSettingsService::getBool('product_extra_gallery_enabled')) {
            $relations[] = 'defaultGalleryImages';
            $relations[] = 'secondaryGalleryImages';
        } else {
            $relations[] = 'defaultGalleryImages';
        }

        if (WebshopSettingsService::getBool('product_document_upload_enabled')) {
            $relations['productDocuments'] = function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            };
        }

        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $relations[] = 'label';
        }

        $product->load($relations);

        /*
           A "Hasonló termékek" közül KI kell zárni a saját variációit: azok
           ugyanannak a terméknek a változatai, és a termékoldalon már ott vannak
           a Variációk blokkban. Korábban ide is beestek, mert csak a saját ID-t
           zártuk ki, a kategória viszont ugyanaz.
        */
        $ownVariationGroup = $product->variations()->pluck('webshop_products.id')->push($product->id)->all();

        $similarQuery = WebshopProduct::active()
            ->where('category_id', $product->category_id)
            ->whereNotIn('id', $ownVariationGroup)
            ->limit(8);

        if (WebshopSettingsService::getBool('admin_product_label_assignment_enabled')) {
            $similarQuery->with('label');
        }

        $similarProducts = $similarQuery->get();

        return view('site.webshop.products.show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
            'isProductPage' => true,
        ]);
    }
}
