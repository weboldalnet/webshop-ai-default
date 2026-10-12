<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ártörténet az akciós árak jogszerű feltüntetéséhez (uniós Omnibusz-irányelv,
 * 4/2009. (I. 30.) NFGM–SZMM együttes rendelet).
 *
 * Árcsökkentés hirdetésekor a „korábbi ár” az akciót megelőző 30 nap legalacsonyabb
 * eladási ára. Ehhez minden árváltozást naplózunk (webshop_product_price_history), az
 * akció indulásakor pedig a terméken rögzítjük a referenciaárat (sale_reference_price)
 * és az akció kezdetét (sale_started_at). Lépcsőzetes akciónál a referencia az első
 * csökkentés előtti ár marad.
 *
 * Kiinduló állapot: minden termék mostani eladási ára bekerül a naplóba. A már futó
 * akcióknál a referencia a termék alapára – korábbi adat híján ez a legjobb közelítés.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('public.webshop_product_price_history')) {
            Schema::create('public.webshop_product_price_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                // A vásárló által fizetendő ár (akciós ár, ha van, különben az alapár)
                $table->decimal('price', 12, 2);
                $table->timestamp('recorded_at');

                $table->index(['product_id', 'recorded_at']);
            });

            DB::statement("
                INSERT INTO public.webshop_product_price_history (product_id, price, recorded_at)
                SELECT id, COALESCE(NULLIF(sale_price, 0), price), CURRENT_TIMESTAMP
                FROM public.webshop_products
                WHERE COALESCE(NULLIF(sale_price, 0), price) IS NOT NULL
            ");
        }

        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'sale_reference_price')) {
                $table->decimal('sale_reference_price', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'sale_started_at')) {
                $table->timestamp('sale_started_at')->nullable();
            }
        });

        DB::statement("
            UPDATE public.webshop_products
            SET sale_reference_price = price, sale_started_at = CURRENT_TIMESTAMP
            WHERE sale_price > 0 AND price > sale_price AND sale_reference_price IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            foreach (['sale_reference_price', 'sale_started_at'] as $column) {
                if (Schema::hasColumn('public.webshop_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('public.webshop_product_price_history');
    }
};
