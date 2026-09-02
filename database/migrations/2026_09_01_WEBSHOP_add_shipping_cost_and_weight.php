<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Szállítási díj a rendelésen, és súly a terméken / rendelési tételen.
 *
 * - shipping_cost: eddig a szállítási díj sehol nem került a rendelés összegébe,
 *   pedig a providerek ki tudják számolni. Külön oszlopban tároljuk, hogy a
 *   total_price-ból is látszódjon, mennyi belőle a szállítás.
 * - weight: a futárszolgálati címkéhez (GLS) kötelező a csomag súlya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('total_price');
            }
        });

        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'weight')) {
                // kg, három tizedes: a könnyű termékek miatt (pl. 0.250)
                $table->decimal('weight', 10, 3)->nullable();
            }
        });

        Schema::table('public.webshop_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_order_items', 'weight')) {
                // A rendeléskori súly, hogy a későbbi termék-módosítás ne írja felül
                $table->decimal('weight', 10, 3)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
        });

        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_products', 'weight')) {
                $table->dropColumn('weight');
            }
        });

        Schema::table('public.webshop_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_order_items', 'weight')) {
                $table->dropColumn('weight');
            }
        });
    }
};
