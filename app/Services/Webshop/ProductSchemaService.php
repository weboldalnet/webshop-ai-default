<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class ProductSchemaService
{
    public function generate(WebshopProduct $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => strip_tags($product->short_desc ?? $product->description ?? $product->name),
            'image' => $product->primary_image ? url($product->primary_image) : null,
            'url' => route('site.webshop.products.show', $product),
        ];

        if ($product->sku) {
            $schema['sku'] = $product->sku;
        }

        if ($product->price) {
            $offers = [
                '@type' => 'Offer',
                'price' => number_format($product->sale_price ?? $product->price, 2, '.', ''),
                'priceCurrency' => 'HUF',
                'availability' => $product->stock_enabled && $product->stock_quantity <= 0 ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
                'url' => route('site.webshop.products.show', $product),
            ];
            $schema['offers'] = $offers;
        }

        return $schema;
    }

    public function generateJson(WebshopProduct $product): string
    {
        return json_encode($this->generate($product), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
