<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A beváltott kupon adatai a rendeléshez.
 *
 * A tábla a webshop csomagé, ezért a bővítése is ide való – a kupon
 * csomagban csomag-sorrend-függő lenne, hogy melyik migráció fut előbb.
 *
 * Miért van a kód és a felosztás is eltárolva, nem csak a coupon_id:
 * a kupon később átszerkeszthető vagy törölhető, a leadott rendeléshez
 * viszont annak kell tartoznia, ami a leadás pillanatában érvényes volt.
 * Ugyanaz az elv, mint az elállási kérelem tételeinél és a pénztári
 * kérdőív kérdésszövegeinél.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_orders', 'coupon_id')) {
                $table->integer('coupon_id')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('public.webshop_orders', 'coupon_code')) {
                $table->string('coupon_code', 64)->nullable()->after('coupon_id');
            }
            if (!Schema::hasColumn('public.webshop_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('coupon_code');
            }
            if (!Schema::hasColumn('public.webshop_orders', 'coupon_data')) {
                $table->json('coupon_data')->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            foreach (['coupon_id', 'coupon_code', 'discount_amount', 'coupon_data'] as $column) {
                if (Schema::hasColumn('public.webshop_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
