<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Az AI-fordítás futásai (Webshop › Fordítások, és a szerkesztők „Fordítás” gombja — az egy tételes futás).
 *
 * - webshop_translation_runs: egy indítás beállításai (nyelvek, mód, tartalmak) és összesítői. Az állapot az
 *   adatbázisban van, nem a gyorsítótárban: bezárt fül vagy elfogyott kredit után is folytatható.
 * - webshop_translation_run_items: tétel = egy rekord egy nyelven (type, record_id, locale); a böngésző lépésenként
 *   kéri a következőt.
 * - webshop_translation_backups: a felülírt fordítások előző értéke — a futás vagy egy tétel visszavonásához. A
 *   new_value_hash az AI által írt érték hash-e: ha azóta valaki átírta, a visszavonás azt a mezőt nem bántja.
 *
 * Postgres 9.4: nincs CREATE INDEX IF NOT EXISTS, ezért táblánként egy őrzött blokk.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('public.webshop_translation_runs')) {
            DB::statement("
                CREATE TABLE public.webshop_translation_runs (
                    id bigserial PRIMARY KEY,
                    admin_id integer NULL,
                    status varchar(20) NOT NULL DEFAULT 'pending',
                    options text NULL,
                    scope_label varchar(255) NULL,
                    total_items integer NOT NULL DEFAULT 0,
                    done_items integer NOT NULL DEFAULT 0,
                    error_items integer NOT NULL DEFAULT 0,
                    skipped_items integer NOT NULL DEFAULT 0,
                    translated_fields integer NOT NULL DEFAULT 0,
                    characters integer NOT NULL DEFAULT 0,
                    ai_calls integer NOT NULL DEFAULT 0,
                    error text NULL,
                    created_at timestamp(0) NULL,
                    updated_at timestamp(0) NULL,
                    finished_at timestamp(0) NULL
                )
            ");
            DB::statement('CREATE INDEX webshop_translation_runs_status ON public.webshop_translation_runs (status)');
        }

        if (!Schema::hasTable('public.webshop_translation_run_items')) {
            DB::statement("
                CREATE TABLE public.webshop_translation_run_items (
                    id bigserial PRIMARY KEY,
                    run_id bigint NOT NULL REFERENCES public.webshop_translation_runs (id) ON DELETE CASCADE,
                    type varchar(40) NOT NULL,
                    record_id bigint NOT NULL DEFAULT 0,
                    locale varchar(8) NOT NULL,
                    status varchar(20) NOT NULL DEFAULT 'pending',
                    fields_done integer NOT NULL DEFAULT 0,
                    fields_error integer NOT NULL DEFAULT 0,
                    message text NULL,
                    created_at timestamp(0) NULL,
                    updated_at timestamp(0) NULL
                )
            ");
            DB::statement('CREATE INDEX webshop_translation_run_items_run ON public.webshop_translation_run_items (run_id, status)');
        }

        if (!Schema::hasTable('public.webshop_translation_backups')) {
            DB::statement("
                CREATE TABLE public.webshop_translation_backups (
                    id bigserial PRIMARY KEY,
                    run_id bigint NULL,
                    run_item_id bigint NULL,
                    type varchar(40) NOT NULL,
                    record_id bigint NOT NULL DEFAULT 0,
                    locale varchar(8) NOT NULL,
                    field varchar(200) NOT NULL,
                    old_value text NULL,
                    old_source_hash char(40) NULL,
                    old_origin varchar(10) NULL,
                    new_value_hash char(40) NULL,
                    admin_id integer NULL,
                    created_at timestamp(0) NULL
                )
            ");
            DB::statement('CREATE INDEX webshop_translation_backups_item ON public.webshop_translation_backups (run_item_id)');
            DB::statement('CREATE INDEX webshop_translation_backups_record ON public.webshop_translation_backups (type, record_id, locale)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.webshop_translation_backups');
        DB::statement('DROP TABLE IF EXISTS public.webshop_translation_run_items');
        DB::statement('DROP TABLE IF EXISTS public.webshop_translation_runs');
    }
};
