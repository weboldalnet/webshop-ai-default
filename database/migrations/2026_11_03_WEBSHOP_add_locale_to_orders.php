<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A rendelés nyelve (a pénztár nyelve a leadáskor): ezen a nyelven megy a visszaigazoló és az elállási levél,
 * a számla és a fizetési oldal. Nullázható: a meglévő rendelések az alapnyelvűek (üres = alapnyelv).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public.webshop_orders') && !Schema::hasColumn('public.webshop_orders', 'locale')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->string('locale', 8)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('public.webshop_orders', 'locale')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
    }
};
