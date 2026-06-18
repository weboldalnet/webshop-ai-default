<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. webshop_products bővítése
        Schema::table('public.webshop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_products', 'short_desc')) {
                $table->text('short_desc')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'secondary_name')) {
                $table->string('secondary_name')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_products', 'crm_identifier')) {
                $table->string('crm_identifier')->nullable();
            }
        });

        // 2. webshop_product_gallery_images bővítése
        Schema::table('public.webshop_product_gallery_images', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_product_gallery_images', 'gallery_type')) {
                $table->string('gallery_type')->default('default')->index();
            }
        });

        // 3. webshop_product_categories bővítése
        Schema::table('public.webshop_product_categories', function (Blueprint $table) {
            // Megjegyzés: a WebshopCategory modell a 'public.webshop_categories' táblát használja
        });

        Schema::table('public.webshop_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_categories', 'card_width_units')) {
                $table->unsignedTinyInteger('card_width_units')->default(1);
            }
            if (!Schema::hasColumn('public.webshop_categories', 'list_image_mode')) {
                $table->string('list_image_mode')->nullable()->default('icon');
            }
            if (!Schema::hasColumn('public.webshop_categories', 'list_image_product_id')) {
                $table->unsignedBigInteger('list_image_product_id')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_categories', 'list_image_path')) {
                $table->string('list_image_path')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_categories', 'list_image_cropped_path')) {
                $table->string('list_image_cropped_path')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_categories', 'google_merchant_id')) {
                $table->string('google_merchant_id')->nullable();
            }
            if (!Schema::hasColumn('public.webshop_categories', 'facebook_merchant_id')) {
                $table->string('facebook_merchant_id')->nullable();
            }
        });

        // 4. webshop_product_documents tábla
        if (!Schema::hasTable('public.webshop_product_documents')) {
            Schema::create('public.webshop_product_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('name');
                $table->string('type'); // link, file
                $table->string('url')->nullable();
                $table->string('file_path')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. webshop_custom_contents tábla (email és thank you page tartalmak)
        if (!Schema::hasTable('public.webshop_custom_contents')) {
            Schema::create('public.webshop_custom_contents', function (Blueprint $table) {
                $table->id();
                $table->string('type')->index(); // email, thank_you
                $table->string('checkout_mode')->nullable()->index();
                $table->string('payment_method')->nullable()->index();
                $table->string('shipping_method')->nullable()->index();
                $table->string('title')->nullable();
                $table->longText('content')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 6. webshop_tracking_scripts tábla
        if (!Schema::hasTable('public.webshop_tracking_scripts')) {
            Schema::create('public.webshop_tracking_scripts', function (Blueprint $table) {
                $table->id();
                $table->string('page_type')->index(); // product_list, product, checkout, thank_you, homepage
                $table->string('name')->nullable();
                $table->longText('script');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public.webshop_tracking_scripts');
        Schema::dropIfExists('public.webshop_custom_contents');
        Schema::dropIfExists('public.webshop_product_documents');

        Schema::table('public.webshop_categories', function (Blueprint $table) {
            $table->dropColumn([
                'card_width_units', 'list_image_mode', 'list_image_product_id',
                'list_image_path', 'list_image_cropped_path',
                'google_merchant_id', 'facebook_merchant_id'
            ]);
        });

        Schema::table('public.webshop_product_gallery_images', function (Blueprint $table) {
            $table->dropColumn('gallery_type');
        });

        Schema::table('public.webshop_products', function (Blueprint $table) {
            $table->dropColumn(['short_desc', 'secondary_name', 'crm_identifier']);
        });
    }
};
