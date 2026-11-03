<?php

namespace Weboldalnet\WebshopAiDefault\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A webshop KÖZÖS adatbázis-kapcsolata.
 *
 * Többnyelvű core-projektben minden nyelvnek saját adatbázisa van, és a `database.default` kérésenként a
 * látogató (adminban a kiválasztott) nyelvére vált. A webshop adatai — termék, ár, készlet, rendelés, kupon,
 * beállítás — viszont nyelvtől függetlenek: mindig az alapnyelvi adatbázisban élnek, a lefordított szövegek
 * pedig a `webshop_translations` táblában. Ezt a kapcsolatot használja minden webshop-modell
 * (`UsesWebshopConnection`), és ezen kell nyitni minden nyers lekérdezést és tranzakciót (`db()`), különben egy
 * nem alapnyelvű kérésben a tranzakció másik adatbázison futna, mint az írás.
 *
 * Forrás: `config('webshop.connection')`, különben a core `App\Helpers\Lang::DEFAULT_DB` (ugyanígy rögzíti a core a
 * saját közös adatait: Contact, Settings, Admin). Ha egyik sincs (pl. csomagteszt), az alapértelmezett kapcsolat.
 * Egynyelvű projektben ez ugyanaz az adatbázis, mint eddig — a működés nem változik.
 */
final class WebshopConnection
{
    private static bool $resolved = false;

    private static ?string $name = null;

    public static function name(): ?string
    {
        if (!self::$resolved) {
            $name = config('webshop.connection');

            if (!$name && class_exists(\App\Helpers\Lang::class) && defined(\App\Helpers\Lang::class . '::DEFAULT_DB')) {
                $name = \App\Helpers\Lang::DEFAULT_DB;
            }

            // Csak létező kapcsolatra rögzítünk; ismeretlen névnél marad az alapértelmezett.
            self::$name = $name && config('database.connections.' . $name) ? $name : null;
            self::$resolved = true;
        }

        return self::$name;
    }

    public static function db(): ConnectionInterface
    {
        return DB::connection(self::name());
    }

    public static function schema(): Builder
    {
        return Schema::connection(self::name());
    }

    /** Tesztekhez: a kapcsolatnév újraolvasása a configból. */
    public static function flush(): void
    {
        self::$resolved = false;
        self::$name = null;
    }
}
