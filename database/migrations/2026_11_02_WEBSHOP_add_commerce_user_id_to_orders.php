<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A rendelés vásárlói fiókhoz kötése (weboldalnet/commerce-users). Nullázható: a vendég-
 * rendeléseknél üres, és a fiók törlésekor is kiürül — a rendelés a megrendelő adataival
 * megmarad. Idegen kulcs SZÁNDÉKOSAN nincs: a felhasználó modul külön csomag, telepítése nélkül
 * is le kell tudnia futni ennek a migrációnak.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public.webshop_orders') && !Schema::hasColumn('public.webshop_orders', 'commerce_user_id')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('commerce_user_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('public.webshop_orders', 'commerce_user_id')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->dropIndex(['commerce_user_id']);
                $table->dropColumn('commerce_user_id');
            });
        }
    }
};
