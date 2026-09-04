<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A webshop egyedi nyitóoldalának blokkjai.
 *
 * Miért saját tábla és nem egy JSON a webshop_settings-ben:
 * a blokkok rendezett, elemenként szerkesztett listát alkotnak. Egy JSON-blobot
 * minden mentés teljesen kicserélne, ami két párhuzamos admin-fülnél némán
 * eldobná az egyik munkáját. Ugyanezt a mintát követi a webshop_tracking_scripts.
 *
 * Miért van a három banner-kép KÜLÖN OSZLOPBAN a settings helyett:
 * kép cseréjekor és blokk törlésekor a régi fájlt takarítani kell, az árva
 * képek keep-listáját pedig az adatbázisból kell építeni. Külön oszlopból ez
 * egy SELECT; JSON-ból mezőnkénti kibontás lenne. Az oszlopnevek szándékosan
 * általánosak (img_*, nem banner_*), hogy egy későbbi képes blokktípus is
 * használhassa őket új migráció nélkül.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public.webshop_home_blocks')) {
            return;
        }

        Schema::create('public.webshop_home_blocks', function (Blueprint $table) {
            $table->id();

            // products_manual | products_category | categories | banner | text
            $table->string('type', 30);
            $table->string('title')->nullable();

            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();

            // scroll | multi_row – csak a kártyás típusoknál van jelentése
            $table->string('layout', 20)->default('multi_row');

            $table->string('link_url', 500)->nullable();
            $table->string('link_label', 100)->nullable();

            $table->string('img_desktop', 500)->nullable();
            $table->string('img_tablet', 500)->nullable();
            $table->string('img_mobile', 500)->nullable();
            $table->string('img_alt')->nullable();

            // Kizárólag a típusfüggő rész (pl. product_ids, category_id, content)
            $table->jsonb('settings')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public.webshop_home_blocks');
    }
};
