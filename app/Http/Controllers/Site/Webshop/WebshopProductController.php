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

        $kifejezes = trim((string) $request->input('q', ''));

        if (mb_strlen($kifejezes) < self::SEARCH_MIN_LENGTH) {
            return response('');
        }

        // ILIKE: a Postgres kis-nagybetű független illesztése.
        // A minta-karaktereket escape-eljük, különben a beírt % vagy _ jel
        // mindenre illeszkedne (a % "bármi", a _ "egy tetszőleges karakter").
        $escapeKarakterek = '%_' . chr(92);
        $minta = '%' . addcslashes($kifejezes, $escapeKarakterek) . '%';

        $query = WebshopProduct::active()
            ->with('category')
            ->where(function ($q) use ($minta) {
                $q->where('name', 'ilike', $minta)
                    ->orWhere('secondary_name', 'ilike', $minta)
                    ->orWhere('sku', 'ilike', $minta)
                    ->orWhere('short_desc', 'ilike', $minta);
            });

        $osszesTalalat = (clone $query)->count();

        $termekek = $query->orderBy('sort_order')->orderBy('id')
            ->limit(self::SEARCH_LIMIT)
            ->get();

        return view('site.webshop.partials.search-results', [
            'searchValue' => $kifejezes,
            'products' => $termekek,
            'resultCount' => $osszesTalalat,
        ])->render();
    }
    public function show(WebshopProduct $product)
    {
        if (!$product->is_active) abort(404);

        $relations = ['category', 'relatedProducts', 'variations', 'productProperties.property'];
        
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

        $similarQuery = WebshopProduct::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
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
