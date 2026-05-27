<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\")
            SELECT 'admin_product_primary_image_mode', 'cropper', 'string', 'product'
            WHERE NOT EXISTS (SELECT 1 FROM public.webshop_settings WHERE key = 'admin_product_primary_image_mode')
        ");
    }

    public function down()
    {
        DB::statement("DELETE FROM public.webshop_settings WHERE key = 'admin_product_primary_image_mode'");
    }
};
