<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elállási kérelmek.
 *
 * A vásárló a rendelésszáma alapján, bejelentkezés nélkül nyújthat be elállási
 * kérelmet – akár a teljes rendelésre, akár csak egyes tételekre.
 *
 * A vevő adatait és a tételek nevét/árát a kérelem beadásakor lemásoljuk:
 * a rendelés vagy a termék későbbi módosítása nem írhatja felül azt, amire
 * a vásárló ténylegesen elállt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('public.webshop_withdrawals')) {
            Schema::create('public.webshop_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                // A rendelés törlése esetén is beazonosítható maradjon
                $table->string('order_number')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                // pending | in_progress | closed
                $table->string('status')->default('pending');
                // A vásárló indoklása (kötelező mező a site oldalon)
                $table->text('reason')->nullable();
                // Teljes rendeléstől áll el, vagy csak egyes tételektől
                $table->boolean('is_full')->default(false);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->text('admin_note')->nullable();
                $table->timestamps();

                $table->index('order_id');
                $table->index('status');
            });
        }

        if (!Schema::hasTable('public.webshop_withdrawal_items')) {
            Schema::create('public.webshop_withdrawal_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('withdrawal_id');
                $table->unsignedBigInteger('order_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_price', 12, 2)->default(0);
                $table->timestamps();

                $table->index('withdrawal_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public.webshop_withdrawal_items');
        Schema::dropIfExists('public.webshop_withdrawals');
    }
};
