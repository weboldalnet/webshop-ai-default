<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO / megosztási mezők a termékekhez — a kategóriákon ez a három oszlop már létezett
 * (`webshop_categories.og_title/og_description/og_img`), a termékeknél viszont nem volt
 * semmi, így a termékoldalak meta leírása és og:image tagje üresen maradt.
 *
 * A mezőnevek SZÁNDÉKOSAN ugyanazok, mint a kategóriánál és a core oldalmodelljeinél
 * (Article, PageSettings, CustomPage): így a sablonok és a szerkesztők egyformán kezelik.
 * Üresen hagyva a megjelenítés a termék nevéből, rövid leírásából és elsődleges képéből
 * állít elő tartalékot (WebshopSeoService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'og_title')) {
                $table->string('og_title')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'og_description')) {
                $table->text('og_description')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'og_img')) {
                $table->string('og_img')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            foreach (['og_title', 'og_description', 'og_img'] as $column) {
                if (Schema::hasColumn('public.webshop_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
