<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mikor ment ki a rendelés-visszaigazoló levél (WebshopCheckoutService::sendConfirmationMail).
 * Online fizetésnél a levelet a sikeres fizetés eseménye küldi, amit a visszahívás és a
 * visszatérő oldal egyszerre is kiválthat — ez az oszlop biztosítja, hogy csak egy levél menjen.
 * A meglévő rendeléseknél üres marad: visszamenőleg nem küldünk semmit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public.webshop_orders') && !Schema::hasColumn('public.webshop_orders', 'confirmation_mail_sent_at')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->timestamp('confirmation_mail_sent_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('public.webshop_orders', 'confirmation_mail_sent_at')) {
            Schema::table('public.webshop_orders', function (Blueprint $table) {
                $table->dropColumn('confirmation_mail_sent_at');
            });
        }
    }
};
