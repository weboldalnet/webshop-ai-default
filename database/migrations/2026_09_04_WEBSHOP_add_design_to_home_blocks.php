<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nyitóoldali blokkok megjelenési beállításai és gombjai.
 *
 * A gombok a projekt meglévő, hero-knál is használt szerkezetét követik
 * (btn.primary / btn.secondary, ikonokkal és új lapon nyitással), hogy az
 * admin.elements.includes.button-multi és a site.elements.items.buttons-2
 * változtatás nélkül működjön rajtuk.
 *
 * A színek szabad HEX értékek, nem osztálynevek: a blokkok háttere a site-on
 * teljes oldalszélességben fut, ahol a meglévő segédosztályok nem adnának
 * elég szabadságot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_home_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_home_blocks', 'bg_color')) {
                $table->string('bg_color', 20)->nullable()->after('layout');
            }
            if (!Schema::hasColumn('public.webshop_home_blocks', 'title_color')) {
                $table->string('title_color', 20)->nullable()->after('bg_color');
            }
            if (!Schema::hasColumn('public.webshop_home_blocks', 'btn_color')) {
                $table->string('btn_color', 20)->default('main')->after('title_color');
            }
            if (!Schema::hasColumn('public.webshop_home_blocks', 'btn_align')) {
                $table->string('btn_align', 40)->default('justify-content-start')->after('btn_color');
            }
            if (!Schema::hasColumn('public.webshop_home_blocks', 'buttons')) {
                $table->jsonb('buttons')->nullable()->after('btn_align');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_home_blocks', function (Blueprint $table) {
            foreach (['bg_color', 'title_color', 'btn_color', 'btn_align', 'buttons'] as $oszlop) {
                if (Schema::hasColumn('public.webshop_home_blocks', $oszlop)) {
                    $table->dropColumn($oszlop);
                }
            }
        });
    }
};
