<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\")
            SELECT 'site_product_list_default_view', 'card', 'string', 'site_category'
            WHERE NOT EXISTS (
                SELECT 1 FROM public.webshop_settings WHERE key = 'site_product_list_default_view'
            )
        ");
    }

    public function down()
    {
        DB::statement("DELETE FROM public.webshop_settings WHERE key = 'site_product_list_default_view'");
    }
};
