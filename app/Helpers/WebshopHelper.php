<?php

namespace Weboldalnet\WebshopAiDefault\Helpers;

use Carbon\Carbon;

/**
 * Nyelvfüggő formázás a vásárlói oldalra és a levelekre. Blade-ben a `WsFormat` aliasszal:
 * {{ WsFormat::money($order->total) }}, {{ WsFormat::datetime($order->created_at) }}.
 *
 * A formátum a webshop::common nyelvi fájlból jön; magyarul ugyanazt adja, mint a core hufFormat() (12 990 Ft).
 * Levélben és parancssorban a hívó állítja be a nyelvet (Mail::locale(), App::setLocale()) — ez a segéd a mindenkori
 * app()->getLocale()-t követi.
 */
class WebshopHelper
{
    public static function number($value, int $decimals = 0): string
    {
        return number_format(
            (float) $value,
            $decimals,
            __('webshop::common.decimal_separator'),
            __('webshop::common.thousands_separator')
        );
    }

    public static function money($amount, int $decimals = 0): string
    {
        return __('webshop::common.money', ['amount' => self::number($amount, $decimals)]);
    }

    public static function date($value): string
    {
        return self::formatDate($value, 'date');
    }

    public static function datetime($value): string
    {
        return self::formatDate($value, 'datetime');
    }

    protected static function formatDate($value, string $format): string
    {
        if (empty($value)) {
            return '';
        }

        $date = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value);

        return $date->format(__('webshop::common.' . $format));
    }
}
