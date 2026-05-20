<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\") VALUES
            ('site_product_list_default_view', 'card', 'string', 'site_category')
            ON CONFLICT (key) DO NOTHING
        ");
    }

    public function down()
    {
        DB::statement("DELETE FROM public.webshop_settings WHERE key = 'site_product_list_default_view'");
    }
};
