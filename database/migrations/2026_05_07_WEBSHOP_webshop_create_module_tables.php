<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class WebshopCreateModuleTables extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE TABLE public.webshop_property_categories (
                id SERIAL PRIMARY KEY,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                filter_enabled BOOLEAN NOT NULL DEFAULT FALSE,
                filter_type VARCHAR(20) NOT NULL DEFAULT 'checkbox',
                suffix VARCHAR(50) DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_wpc_slug ON public.webshop_property_categories (slug) WHERE deleted_at IS NULL");
        DB::statement("CREATE INDEX idx_wpc_active ON public.webshop_property_categories (is_active)");
        DB::statement("CREATE INDEX idx_wpc_sort ON public.webshop_property_categories (sort_order)");

        DB::statement("
            CREATE TABLE public.webshop_properties (
                id SERIAL PRIMARY KEY,
                property_category_id INT NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_wp_category FOREIGN KEY (property_category_id) REFERENCES public.webshop_property_categories (id) ON DELETE CASCADE
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_wp_slug ON public.webshop_properties (slug, property_category_id) WHERE deleted_at IS NULL");
        DB::statement("CREATE INDEX idx_wp_category ON public.webshop_properties (property_category_id)");
        DB::statement("CREATE INDEX idx_wp_sort ON public.webshop_properties (sort_order)");

        DB::statement("
            CREATE TABLE public.webshop_categories (
                id SERIAL PRIMARY KEY,
                parent_id INT DEFAULT NULL,
                name_singular TEXT NOT NULL,
                name_plural TEXT NOT NULL,
                slug TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                og_title TEXT DEFAULT NULL,
                og_description TEXT DEFAULT NULL,
                og_img TEXT DEFAULT NULL,
                icon TEXT DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_wc_parent FOREIGN KEY (parent_id) REFERENCES public.webshop_categories (id) ON DELETE SET NULL
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_wc_slug ON public.webshop_categories (slug) WHERE deleted_at IS NULL");
        DB::statement("CREATE INDEX idx_wc_parent ON public.webshop_categories (parent_id)");
        DB::statement("CREATE INDEX idx_wc_sort ON public.webshop_categories (sort_order)");

        DB::statement("
            CREATE TABLE public.webshop_category_property_category (
                id SERIAL PRIMARY KEY,
                category_id INT NOT NULL,
                property_category_id INT NOT NULL,
                show_on_product_card BOOLEAN NOT NULL DEFAULT FALSE,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wcpc_cat FOREIGN KEY (category_id) REFERENCES public.webshop_categories (id) ON DELETE CASCADE,
                CONSTRAINT fk_wcpc_pcat FOREIGN KEY (property_category_id) REFERENCES public.webshop_property_categories (id) ON DELETE CASCADE,
                CONSTRAINT uq_wcpc_pair UNIQUE (category_id, property_category_id)
            )
        ");

        DB::statement("
            CREATE TABLE public.webshop_related_categories (
                id SERIAL PRIMARY KEY,
                category_id INT NOT NULL,
                related_category_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wrc_cat FOREIGN KEY (category_id) REFERENCES public.webshop_categories (id) ON DELETE CASCADE,
                CONSTRAINT fk_wrc_rel FOREIGN KEY (related_category_id) REFERENCES public.webshop_categories (id) ON DELETE CASCADE,
                CONSTRAINT uq_wrc_pair UNIQUE (category_id, related_category_id),
                CONSTRAINT chk_wrc_self CHECK (category_id <> related_category_id)
            )
        ");

        DB::statement("
            CREATE TABLE public.webshop_products (
                id SERIAL PRIMARY KEY,
                category_id INT NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                primary_image TEXT DEFAULT NULL,
                primary_image_thumb TEXT DEFAULT NULL,
                sku VARCHAR(100) DEFAULT NULL,
                stock_enabled BOOLEAN NOT NULL DEFAULT FALSE,
                stock_quantity INT DEFAULT NULL,
                price NUMERIC(12,2) DEFAULT NULL,
                sale_price NUMERIC(12,2) DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_wprod_cat FOREIGN KEY (category_id) REFERENCES public.webshop_categories (id) ON DELETE CASCADE
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_wprod_slug ON public.webshop_products (slug) WHERE deleted_at IS NULL");
        DB::statement("CREATE INDEX idx_wprod_cat ON public.webshop_products (category_id)");
        DB::statement("CREATE INDEX idx_wprod_sort ON public.webshop_products (sort_order)");

        DB::statement("
            CREATE TABLE public.webshop_product_properties (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                property_category_id INT NOT NULL,
                property_id INT DEFAULT NULL,
                number_value NUMERIC(12,2) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wpp_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE,
                CONSTRAINT fk_wpp_pcat FOREIGN KEY (property_category_id) REFERENCES public.webshop_property_categories (id) ON DELETE CASCADE,
                CONSTRAINT fk_wpp_prop FOREIGN KEY (property_id) REFERENCES public.webshop_properties (id) ON DELETE CASCADE
            )
        ");
        DB::statement("CREATE INDEX idx_wpp_prod ON public.webshop_product_properties (product_id)");
        DB::statement("CREATE INDEX idx_wpp_pcat ON public.webshop_product_properties (property_category_id)");

        DB::statement("
            CREATE TABLE public.webshop_product_related (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                related_product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wpr_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE,
                CONSTRAINT fk_wpr_rel FOREIGN KEY (related_product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE,
                CONSTRAINT uq_wpr_pair UNIQUE (product_id, related_product_id),
                CONSTRAINT chk_wpr_self CHECK (product_id <> related_product_id)
            )
        ");

        DB::statement("
            CREATE TABLE public.webshop_product_variations (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                variation_product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wpv_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE,
                CONSTRAINT fk_wpv_var FOREIGN KEY (variation_product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE,
                CONSTRAINT uq_wpv_pair UNIQUE (product_id, variation_product_id),
                CONSTRAINT chk_wpv_self CHECK (product_id <> variation_product_id)
            )
        ");

        DB::statement("
            CREATE TABLE public.webshop_product_gallery_images (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                image TEXT NOT NULL,
                image_thumb TEXT NOT NULL,
                alt TEXT DEFAULT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                CONSTRAINT fk_wpgi_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE CASCADE
            )
        ");
        DB::statement("CREATE INDEX idx_wpgi_prod ON public.webshop_product_gallery_images (product_id)");

        DB::statement("
            CREATE TABLE public.webshop_orders (
                id SERIAL PRIMARY KEY,
                order_number VARCHAR(50) NOT NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'pending',
                customer_name TEXT DEFAULT NULL,
                customer_email TEXT DEFAULT NULL,
                customer_phone TEXT DEFAULT NULL,
                billing_data TEXT DEFAULT NULL,
                shipping_data TEXT DEFAULT NULL,
                total_price NUMERIC(12,2) NOT NULL DEFAULT 0,
                currency VARCHAR(10) NOT NULL DEFAULT 'HUF',
                is_completed BOOLEAN NOT NULL DEFAULT FALSE,
                completed_at TIMESTAMP DEFAULT NULL,
                admin_note TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_wo_number ON public.webshop_orders (order_number)");
        DB::statement("CREATE INDEX idx_wo_status ON public.webshop_orders (status)");

        DB::statement("
            CREATE TABLE public.webshop_order_items (
                id SERIAL PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT DEFAULT NULL,
                product_name TEXT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price NUMERIC(12,2) NOT NULL DEFAULT 0,
                total_price NUMERIC(12,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_woi_order FOREIGN KEY (order_id) REFERENCES public.webshop_orders (id) ON DELETE CASCADE,
                CONSTRAINT fk_woi_prod FOREIGN KEY (product_id) REFERENCES public.webshop_products (id) ON DELETE SET NULL
            )
        ");

        DB::statement("
            CREATE TABLE public.webshop_settings (
                id SERIAL PRIMARY KEY,
                key VARCHAR(100) NOT NULL,
                value TEXT DEFAULT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'boolean',
                \"group\" VARCHAR(50) NOT NULL DEFAULT 'general',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        DB::statement("CREATE UNIQUE INDEX idx_ws_key ON public.webshop_settings (key)");

        DB::statement("
            INSERT INTO public.webshop_settings (key, value, type, \"group\") VALUES
            ('category_icon_enabled', 'false', 'boolean', 'category'),
            ('category_parent_enabled', 'false', 'boolean', 'category'),
            ('category_related_enabled', 'false', 'boolean', 'category'),
            ('category_product_card_properties_enabled', 'false', 'boolean', 'category'),
            ('product_stock_enabled', 'false', 'boolean', 'product'),
            ('product_related_enabled', 'false', 'boolean', 'product'),
            ('product_price_enabled', 'true', 'boolean', 'product'),
            ('product_gallery_enabled', 'false', 'boolean', 'product'),
            ('product_variations_enabled', 'false', 'boolean', 'product')
        ");

        DB::commit();
    }

    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS public.webshop_order_items CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_orders CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_product_gallery_images CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_product_variations CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_product_related CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_product_properties CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_products CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_related_categories CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_category_property_category CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_categories CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_properties CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_property_categories CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.webshop_settings CASCADE");
    }
}
