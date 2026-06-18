<?php

namespace Weboldalnet\WebshopAiDefault\Console\Commands;

use App\Helpers\Lang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use XMLWriter;

class GenerateMerchantFeeds extends Command
{
    protected $signature = 'webshop:generate-merchant-feeds';
    protected $description = 'Generate Google and Facebook Merchant XML feeds for all active languages.';

    public function handle()
    {
        $languages = Lang::getLanguages();
        $basePath = 'public/feeds';

        if (!Storage::exists($basePath)) {
            Storage::makeDirectory($basePath);
        }

        foreach ($languages as $lang) {
            $this->generateFeed($lang, 'google');
            $this->generateFeed($lang, 'facebook');
        }

        return 0;
    }

    private function generateFeed(string $lang, string $type)
    {
        $filename = "{$type}-merchant-{$lang}.xml";
        $path = "public/feeds/{$filename}";

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        if ($type === 'google') {
            $xml->startElement('rss');
            $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
            $xml->writeAttribute('version', '2.0');
            $xml->startElement('channel');
            $xml->writeElement('title', config('app.name') . " - Google Merchant Feed ({$lang})");
            $xml->writeElement('link', url('/'));
            $xml->writeElement('description', "Termék feed a Google Merchant Center számára.");
        } else {
            // Facebook
            $xml->startElement('rss');
            $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
            $xml->writeAttribute('version', '2.0');
            $xml->startElement('channel');
            $xml->writeElement('title', config('app.name') . " - Facebook Merchant Feed ({$lang})");
            $xml->writeElement('link', url('/'));
            $xml->writeElement('description', "Termék feed a Facebook Catalog számára.");
        }

        $merchantIdField = "{$type}_merchant_id";
        $categories = WebshopCategory::whereNotNull($merchantIdField)->pluck('id')->toArray();

        if (empty($categories)) {
            $this->warn("Nincs kategória beállítva a(z) {$type} feedhez ({$lang}).");
            // Mégis létrehozzuk az üres vázat, vagy kilépünk
        }

        $products = WebshopProduct::active()
            ->whereIn('category_id', $categories)
            ->with(['category', 'label'])
            ->get();

        foreach ($products as $product) {
            $xml->startElement('item');
            $xml->writeElement('g:id', $product->id);
            $xml->writeElement('g:title', $product->name);
            $xml->writeElement('g:description', strip_tags($product->short_desc ?? $product->description ?? $product->name));
            $xml->writeElement('g:link', route('site.webshop.products.show', $product));
            $xml->writeElement('g:image_link', $product->primary_image ? url($product->primary_image) : '');
            $xml->writeElement('g:availability', $product->stock_enabled && $product->stock_quantity <= 0 ? 'out of stock' : 'in stock');

            if ($product->price) {
                $currency = 'HUF'; // Alapértelmezett, bővíthető ha van currency a projektben
                $xml->writeElement('g:price', number_format($product->price, 2, '.', '') . ' ' . $currency);
                if ($product->sale_price && $product->sale_price < $product->price) {
                    $xml->writeElement('g:sale_price', number_format($product->sale_price, 2, '.', '') . ' ' . $currency);
                }
            }

            $xml->writeElement('g:brand', config('app.name'));
            $xml->writeElement('g:google_product_category', $product->category->{$merchantIdField} ?? '');

            if ($product->sku) {
                $xml->writeElement('g:mpn', $product->sku);
            }

            $xml->writeElement('g:condition', 'new');

            $xml->endElement(); // item
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss
        $xml->endDocument();

        Storage::put($path, $xml->outputMemory());

        $this->info("Nyelv: {$lang} | Típus: {$type} | Termékek száma: " . $products->count() . " | Fájl: " . Storage::url($path));
    }
}
