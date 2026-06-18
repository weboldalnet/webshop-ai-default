<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCommerceFieldsToOrders extends Migration
{
    public function up()
    {
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='type') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'order';
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='customer_company') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN customer_company TEXT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='customer_tax_number') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN customer_tax_number TEXT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='note') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN note TEXT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='payment_method') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN payment_method VARCHAR(100) DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='payment_status') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid';
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='invoice_status') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN invoice_status VARCHAR(50) NOT NULL DEFAULT 'not_required';
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='shipping_method') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN shipping_method VARCHAR(100) DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='shipping_status') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN shipping_status VARCHAR(50) NOT NULL DEFAULT 'not_required';
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='commerce_payment_transaction_id') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN commerce_payment_transaction_id BIGINT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='commerce_invoice_document_id') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN commerce_invoice_document_id BIGINT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='commerce_shipment_id') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN commerce_shipment_id BIGINT DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='paid_at') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN paid_at TIMESTAMP DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='invoiced_at') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN invoiced_at TIMESTAMP DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='webshop_orders' AND column_name='shipped_at') THEN
                    ALTER TABLE public.webshop_orders ADD COLUMN shipped_at TIMESTAMP DEFAULT NULL;
                END IF;
            END
            $$
        ");

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_payment_method') THEN
                    CREATE INDEX idx_wo_payment_method ON public.webshop_orders (payment_method);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_payment_status') THEN
                    CREATE INDEX idx_wo_payment_status ON public.webshop_orders (payment_status);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_invoice_status') THEN
                    CREATE INDEX idx_wo_invoice_status ON public.webshop_orders (invoice_status);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_shipping_method') THEN
                    CREATE INDEX idx_wo_shipping_method ON public.webshop_orders (shipping_method);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_shipping_status') THEN
                    CREATE INDEX idx_wo_shipping_status ON public.webshop_orders (shipping_status);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='webshop_orders' AND indexname='idx_wo_type') THEN
                    CREATE INDEX idx_wo_type ON public.webshop_orders (type);
                END IF;
            END
            $$
        ");
    }

    public function down()
    {
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS type");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS customer_company");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS customer_tax_number");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS note");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS payment_method");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS payment_status");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS invoice_status");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS shipping_method");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS shipping_status");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS commerce_payment_transaction_id");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS commerce_invoice_document_id");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS commerce_shipment_id");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS paid_at");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS invoiced_at");
        DB::statement("ALTER TABLE public.webshop_orders DROP COLUMN IF EXISTS shipped_at");
    }
}
