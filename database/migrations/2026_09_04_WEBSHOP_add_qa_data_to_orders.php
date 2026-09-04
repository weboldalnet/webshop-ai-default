<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A pénztárban kitöltött kérdőív (ContactQa) válaszai a rendeléshez.
 *
 * A kérdés SZÖVEGÉT is eltároljuk a válasz mellé, nem csak a kérdőív
 * azonosítóját: a kérdőív később átszerkeszthető vagy törölhető, a leadott
 * rendeléshez viszont annak kell tartoznia, amit a vásárló ténylegesen látott
 * és megválaszolt. Ugyanaz az elv, mint az elállási kérelem tételeinél.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_orders', 'qa_data')) {
                $table->json('qa_data')->nullable()->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_orders', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_orders', 'qa_data')) {
                $table->dropColumn('qa_data');
            }
        });
    }
};
