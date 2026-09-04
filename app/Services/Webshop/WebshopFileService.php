<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WebshopFileService
{
    public static function saveProductImage(UploadedFile $file, string $name, $width, $height): string
    {
        $mode = WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper');

        if ($mode === 'simple') {
            return WebshopImageService::saveSimpleImage($file, 'webshop/products', $name, $width, $height);
        }

        if ($mode === 'simple_white') {
            return WebshopImageService::saveSimpleImage($file, 'webshop/products', $name, $width, $height, true);
        }

        return ImageService::saveCustomImage($file, 'webshop/products', $name, $width, $height);
    }

    public static function saveProductImageThumbnail(UploadedFile $file, string $name, $width, $height): string
    {
        $mode = WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper');

        if ($mode === 'simple') {
            return WebshopImageService::saveSimpleImage($file, 'webshop/products', ($name . '-thumb'), $width, $height);
        }

        if ($mode === 'simple_white') {
            return WebshopImageService::saveSimpleImage($file, 'webshop/products', ($name . '-thumb'), $width, $height, true);
        }

        return ImageService::saveCustomImage($file, 'webshop/products', ($name . '-thumb'), $width, $height);
    }

    public static function saveGalleryImage(UploadedFile $file, string $name): string
    {
        return ImageService::saveCustomImage($file, 'webshop/products/gallery', $name, 1000);
    }

    public static function saveGalleryImageThumbnail(UploadedFile $file, string $name): string
    {
        return ImageService::saveCustomImage($file, 'webshop/products/gallery', $name, 150);
    }

    /**
     * Nyitóoldali blokk képe (banner asztali/tablet/mobil változata).
     *
     * MAGASSÁG NÉLKÜL mentünk: a saveCustomImage $height paramétere false, így a
     * kép a megadott szélességre skálázódik, a magassága a feltöltött arányból jön.
     * Ez felel meg a magasság nélküli metszőnek – a bannerek magassága szabad.
     */
    public static function saveHomeBlockImage(UploadedFile $file, string $name, int $width): string
    {
        return ImageService::saveCustomImage($file, 'webshop/home-blocks', $name, $width);
    }

    /**
     * Nyitóoldali blokk képének törlése csere vagy blokktörlés után.
     *
     * FIGYELEM: az ImageService::deleteImage() ebben a projektben SZÁNDÉKOSAN
     * nem csinál semmit (Lang::MULTI_LANGUAGE = true), mert ugyanarra a kép-URL-re
     * a másik nyelv adatbázisából is lehet hivatkozás. A hívást mégis megtartjuk,
     * hogy egynyelvű telepítésen a takarítás magától működjön – itt viszont
     * számolni kell azzal, hogy a lecserélt képek fájlként a lemezen maradnak.
     */
    public static function deleteHomeBlockImage(?string $imgUrl): void
    {
        if ($imgUrl) {
            ImageService::deleteImage($imgUrl);
        }
    }
    public static function saveCategoryOgImage(UploadedFile $file, string $name): string
    {
        return ImageService::saveOgImage($file, 'webshop/categories/og', $name);
    }

    public static function saveCategoryIcon(UploadedFile $file, string $name): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = getTransformedString($name) . '-' . getRandNumber() . '.' . $extension;
        $path = 'webshop/categories/icons/' . $fileName;
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
        return getImgStorage() . '/storage/' . $path;
    }
}
