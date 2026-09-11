<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jelző arról, hogy a rendelés levonta-e már a készletet.
 *
 * Enélkül nem lenne eldönthető, mikor szabad visszaírni: egy kétszer törölt
 * (majd visszaállított) rendelés kétszer adná vissza ugyanazokat a darabokat,
 * egy újramentés pedig másodszor is levonná őket.
 *
 * A meglévő rendelések szándékosan false-szal indulnak: azok még a
 * készletkezelés bevezetése előtt keletkeztek, tehát nincs mit visszaírni.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_orders', 'stock_applied')) {
                $table->boolean('stock_applied')->default(false)->after('cod_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_orders', 'stock_applied')) {
                $table->dropColumn('stock_applied');
            }
        });
    }
};
