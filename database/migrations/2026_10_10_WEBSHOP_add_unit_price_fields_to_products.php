<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Egységár-feltüntetéshez a kiszerelés nettó mennyisége és mértékegysége
 * (4/2009. (I. 30.) NFGM–SZMM együttes rendelet). Üresen hagyva nincs egységár –
 * darabáras termékeknél ez a helyes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'unit_quantity')) {
                $table->decimal('unit_quantity', 12, 3)->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'unit_measure')) {
                // g, kg, ml, cl, dl, l, mm, cm, m, m2, m3, pcs – lásd WebshopUnitPriceService::MEASURES
                $table->string('unit_measure', 8)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            foreach (['unit_quantity', 'unit_measure'] as $column) {
                if (Schema::hasColumn('public.webshop_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
