<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kötelező termékinformációk (GPSR, élelmiszer, CLP, energiacímke, textil) termékenként,
 * JSON-ban: {profil: {mező: érték}}. A profilok és mezők leírása:
 * WebshopProductLegalInfoService::PROFILES.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'legal_info')) {
                $table->json('legal_info')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_products', 'legal_info')) {
                $table->dropColumn('legal_info');
            }
        });
    }
};
