<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * Egységár (Ft/kg, Ft/l, Ft/m …) – 4/2009. (I. 30.) NFGM–SZMM együttes rendelet, 98/6/EK irányelv.
 *
 * Tömegre, térfogatra, hosszra, területre mért terméknél az eladási ár mellett az
 * egységárat is fel kell tüntetni. A terméken a kiszerelés nettó mennyisége
 * (unit_quantity) és mértékegysége (unit_measure) adható meg; ebből számoljuk az
 * alapegységre (1 kg, 1 l, 1 m, 1 m², 1 m³) vetített árat. A „db” egység a
 * többdarabos kiszerelésekhez való (pl. 10 db-os csomag → Ft/db).
 *
 * Ha a kiszerelés nincs megadva, semmi sem jelenik meg – darabáras termékeknél
 * (pl. ruha, műszaki cikk) nincs egységár-kötelezettség.
 */
class WebshopUnitPriceService
{
    /**
     * Mértékegység → [megjelenített név, alapegység, szorzó az alapegységre].
     */
    public const MEASURES = [
        'g' => ['label' => 'g', 'base' => 'kg', 'factor' => 0.001],
        'kg' => ['label' => 'kg', 'base' => 'kg', 'factor' => 1],
        'ml' => ['label' => 'ml', 'base' => 'l', 'factor' => 0.001],
        'cl' => ['label' => 'cl', 'base' => 'l', 'factor' => 0.01],
        'dl' => ['label' => 'dl', 'base' => 'l', 'factor' => 0.1],
        'l' => ['label' => 'l', 'base' => 'l', 'factor' => 1],
        'mm' => ['label' => 'mm', 'base' => 'm', 'factor' => 0.001],
        'cm' => ['label' => 'cm', 'base' => 'm', 'factor' => 0.01],
        'm' => ['label' => 'm', 'base' => 'm', 'factor' => 1],
        'm2' => ['label' => 'm²', 'base' => 'm²', 'factor' => 1],
        'm3' => ['label' => 'm³', 'base' => 'm³', 'factor' => 1],
        'pcs' => ['label' => 'db', 'base' => 'db', 'factor' => 1],
    ];

    /**
     * Egységár a termék eladási árából, vagy null, ha nincs kiszerelés megadva.
     *
     * @return array{amount: float, base: string, formatted: string}|null
     */
    public static function forProduct(WebshopProduct $product): ?array
    {
        return self::calculate($product->sellingPrice(), $product->unit_quantity ?? null, $product->unit_measure ?? null);
    }

    public static function calculate(?float $price, $quantity, ?string $measure): ?array
    {
        if ($price === null || $price <= 0 || !$measure || !isset(self::MEASURES[$measure])) {
            return null;
        }

        $baseQuantity = (float) $quantity * self::MEASURES[$measure]['factor'];

        if ($baseQuantity <= 0) {
            return null;
        }

        $amount = $price / $baseQuantity;
        $base = self::MEASURES[$measure]['base'];

        return [
            'amount' => $amount,
            'base' => $base,
            'formatted' => self::formatAmount($amount) . ' Ft/' . $base,
        ];
    }

    /**
     * Kis összegnél (pl. 3,45 Ft/db) két tizedes, egyébként egész forint.
     */
    public static function formatAmount(float $amount): string
    {
        $decimals = $amount < 100 && abs($amount - round($amount)) >= 0.005 ? 2 : 0;

        return number_format($amount, $decimals, ',', ' ');
    }

    /**
     * Admin listához: „500 g” formájú kiszerelés-felirat.
     */
    public static function quantityLabel($quantity, ?string $measure): ?string
    {
        if (!$measure || !isset(self::MEASURES[$measure]) || (float) $quantity <= 0) {
            return null;
        }

        $value = rtrim(rtrim(number_format((float) $quantity, 3, ',', ' '), '0'), ',');

        return $value . ' ' . self::MEASURES[$measure]['label'];
    }
}
