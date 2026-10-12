<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mérőkódok hozzájárulási kategóriája.
 *
 * A nem feltétlenül szükséges sütik és követőkódok csak a látogató hozzájárulásával
 * futhatnak (ePrivacy-szabály, GDPR). Eddig a webshop minden beillesztett kódot
 * feltétel nélkül kiírt. A kategória dönti el, melyik sütiablak-hozzájárulás kell
 * hozzá (statistics / marketing / preferences), a „necessary” azonnal fut.
 *
 * A meglévő kódok szándékosan a legszigorúbb, „marketing” kategóriát kapják: nem
 * tudjuk, mit csinálnak (egy Meta Pixel vagy Hotjar is lehet köztük), így csak
 * hozzájárulás után indulnak. Ha valamelyik valóban feltétlenül szükséges, az adminban
 * átsorolható.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_tracking_scripts', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_tracking_scripts', 'consent_category')) {
                $table->string('consent_category', 32)->default('marketing');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_tracking_scripts', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_tracking_scripts', 'consent_category')) {
                $table->dropColumn('consent_category');
            }
        });
    }
};
