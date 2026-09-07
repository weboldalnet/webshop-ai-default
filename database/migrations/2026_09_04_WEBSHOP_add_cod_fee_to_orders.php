<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Az utánvét-felár eltárolása a rendelésen.
 *
 * Eddig a pénztár kiszámolta és beleszámolta a végösszegbe, de sehol nem
 * tárolta el – így a rendelés utólag nem volt tételesen visszabontható:
 * a végösszegből maradékként kellett kikövetkeztetni. Ez a számlázásnál
 * vált fájóvá, ahol minden forintot néven nevezve kell tételre bontani.
 *
 * Szándékosan nullable, nem default 0: így megkülönböztethető a
 * "nem volt utánvét" (0) és a "még nem tároltuk" (null) eset. A régi
 * rendeléseknél a maradékos levezetés marad érvényben.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_orders', 'cod_fee')) {
                $table->decimal('cod_fee', 12, 2)->nullable()->after('shipping_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_orders', 'cod_fee')) {
                $table->dropColumn('cod_fee');
            }
        });
    }
};
