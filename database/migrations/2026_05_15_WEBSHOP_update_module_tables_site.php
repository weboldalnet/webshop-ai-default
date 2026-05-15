<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::beginTransaction();

        // 1. Kategória tábla bővítése
        DB::statement("ALTER TABLE public.webshop_categories ADD COLUMN show_in_sticky_header BOOLEAN NOT NULL DEFAULT FALSE");

        // 2. Termék vélemények tábla létrehozása
        DB::statement("
            CREATE TABLE public.webshop_product_reviews (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                name TEXT NOT NULL,
                rating INT NOT NULL,
                review TEXT NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_wprv_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE
            )
        ");
        DB::statement("CREATE INDEX idx_wprv_prod ON public.webshop_product_reviews (product_id)");
        DB::statement("CREATE INDEX idx_wprv_active ON public.webshop_product_reviews (is_active)");

        // 3. Rendelés tábla bővítése
        DB::statement("ALTER TABLE public.webshop_orders ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'order'");
        DB::statement("ALTER TABLE public.webshop_orders ADD COLUMN customer_company TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE public.webshop_orders ADD COLUMN customer_tax_number TEXT DEFAULT NULL");
        DB::statement("ALTER TABLE public.webshop_orders ADD COLUMN note TEXT DEFAULT NULL");

        // 4. Új beállítások hozzáadása
        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\") VALUES
            ('site_category_view_switcher_enabled', 'true', 'boolean', 'site_category'),
            ('site_category_cards_per_row', '3', 'string', 'site_category'),
            ('site_related_products_modal_enabled', 'true', 'boolean', 'site_product'),
            ('site_product_reviews_enabled', 'true', 'boolean', 'site_product'),
            ('site_product_prices_visible', 'true', 'boolean', 'site_product'),
            ('site_checkout_mode', 'order', 'string', 'site_checkout'),
            ('site_checkout_company_enabled', 'false', 'boolean', 'site_checkout'),
            ('site_checkout_billing_enabled', 'true', 'boolean', 'site_checkout'),
            ('site_checkout_shipping_enabled', 'true', 'boolean', 'site_checkout'),
            ('site_checkout_tax_number_enabled', 'false', 'boolean', 'site_checkout'),
            ('site_checkout_phone_enabled', 'true', 'boolean', 'site_checkout'),
            ('site_product_compare_enabled', 'true', 'boolean', 'site_general')
        ");

        DB::commit();
    }

    public function down()
    {
        DB::beginTransaction();

        DB::statement("DELETE FROM public.webshop_settings WHERE key IN (
            'site_category_view_switcher_enabled',
            'site_category_cards_per_row',
            'site_related_products_modal_enabled',
            'site_product_reviews_enabled',
            'site_product_prices_visible',
            'site_checkout_mode',
            'site_checkout_company_enabled',
            'site_checkout_billing_enabled',
            'site_checkout_shipping_enabled',
            'site_checkout_tax_number_enabled',
            'site_checkout_phone_enabled',
            'site_product_compare_enabled'
        )");

        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN type");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN customer_company");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN customer_tax_number");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN note");

        DB::statement("DROP TABLE IF EXISTS public.webshop_product_reviews CASCADE");

        DB::statement("ALTER TABLE public.webshop_categories DROP COLUMN show_in_sticky_header");

        DB::commit();
    }
};
