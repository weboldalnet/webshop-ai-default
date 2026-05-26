<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE TABLE public.webshop_product_labels (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                bg_color VARCHAR(20) NOT NULL DEFAULT \'#000000\',
                text_color VARCHAR(20) NOT NULL DEFAULT \'#ffffff\',
                created_at TIMESTAMP WITHOUT TIME ZONE,
                updated_at TIMESTAMP WITHOUT TIME ZONE
            )
        ');

        DB::statement('ALTER TABLE public.webshop_products ADD COLUMN label_id INTEGER REFERENCES public.webshop_product_labels(id) ON DELETE SET NULL');

        // Add settings
        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\")
            SELECT 'admin_product_labels_enabled', 'false', 'boolean', 'admin_product'
            WHERE NOT EXISTS (SELECT 1 FROM public.webshop_settings WHERE key = 'admin_product_labels_enabled')
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.webshop_products DROP COLUMN IF EXISTS label_id');
        DB::statement('DROP TABLE IF EXISTS public.webshop_product_labels');
        DB::statement("DELETE FROM public.webshop_settings WHERE key IN ('admin_product_labels_enabled')");
    }
};
