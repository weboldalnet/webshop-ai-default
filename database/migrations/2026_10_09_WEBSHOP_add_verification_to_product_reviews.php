<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ellenőrzött vásárlói vélemények.
 *
 * Ha a bolt véleményeket mutat, tájékoztatnia kell, hogy ellenőrzi-e, és hogyan,
 * hogy azok a terméket ténylegesen megvásárló vevőktől származnak (Fttv., uniós
 * Omnibusz-szabályok). Az is_verified azt jelzi, hogy a véleményező a rendelésszámával
 * és e-mail-címével igazolta a vásárlást; az order_id a hozzá tartozó rendelés.
 *
 * A meglévő vélemények szándékosan nem ellenőrzöttként indulnak: róluk nem tudjuk,
 * vásárlótól származnak-e.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_product_reviews', 'is_verified')) {
                $table->boolean('is_verified')->default(false);
            }
            if (!Schema::hasColumn('public.webshop_product_reviews', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_product_reviews', function (Blueprint $table) {
            foreach (['is_verified', 'order_id'] as $column) {
                if (Schema::hasColumn('public.webshop_product_reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
