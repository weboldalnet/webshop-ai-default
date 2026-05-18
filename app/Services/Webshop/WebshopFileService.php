<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WebshopFileService
{
    public static function saveProductImage(UploadedFile $file, string $name, $width, $height): string
    {
        return ImageService::saveCustomImage($file, 'webshop/products', $name, $width, $height);
    }

    public static function saveProductImageThumbnail(UploadedFile $file, string $name, $width, $height): string
    {
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
