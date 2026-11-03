<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A webshop lefordított szövegei (többnyelvű projekt). A webshop minden adata az alapnyelvi adatbázisban él
 * (WebshopConnection); a nem alapnyelvű változatok mezőnként, nyelvenként egy-egy sorban:
 *
 * - type: a tartalom fajtája (product, category, property_category, property, label, gallery_image, document,
 *   setting, custom_content, home_block, ui) — WebshopTranslationService::TYPES;
 * - record_id: a rekord azonosítója (beállításnál, felületi szövegnél 0);
 * - field: mezőnév, JSON-mezőnél pont-útvonal (pl. legal_info.general.warnings);
 * - source_hash: az EREDETI szöveg hash-e a fordításkor — ha az eredeti azóta változott, a fordítás elavult;
 * - origin: ai | manual — az AI-tömegfordítás a kézzel javítottat alapból nem írja felül.
 *
 * Slug nyelvenként egyedi (részleges index). Postgres 9.4: nincs CREATE INDEX IF NOT EXISTS, ezért a táblával
 * együtt, egy őrzött blokkban jön létre.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public.webshop_translations')) {
            return;
        }

        DB::statement("
            CREATE TABLE public.webshop_translations (
                id bigserial PRIMARY KEY,
                type varchar(40) NOT NULL,
                record_id bigint NOT NULL DEFAULT 0,
                locale varchar(8) NOT NULL,
                field varchar(200) NOT NULL,
                value text NULL,
                source_hash char(40) NULL,
                origin varchar(10) NOT NULL DEFAULT 'manual',
                admin_id integer NULL,
                created_at timestamp(0) NULL,
                updated_at timestamp(0) NULL
            )
        ");
        DB::statement('CREATE UNIQUE INDEX webshop_translations_unique ON public.webshop_translations (type, record_id, locale, field)');
        DB::statement('CREATE INDEX webshop_translations_locale_type ON public.webshop_translations (locale, type)');
        DB::statement("CREATE UNIQUE INDEX webshop_translations_slug ON public.webshop_translations (type, locale, value) WHERE field = 'slug'");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS public.webshop_translations');
    }
};
