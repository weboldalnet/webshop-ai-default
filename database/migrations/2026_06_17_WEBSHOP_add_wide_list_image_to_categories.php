<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public.webshop_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('public.webshop_categories', 'list_image_cropped_path_wide')) {
                $table->string('list_image_cropped_path_wide')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('public.webshop_categories', function (Blueprint $table) {
            if (Schema::hasColumn('public.webshop_categories', 'list_image_cropped_path_wide')) {
                $table->dropColumn('list_image_cropped_path_wide');
            }
        });
    }
};
