<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as InterventionImage;

class WebshopImageService
{
    /**
     * Mentés egyszerű módon, méretre átméretezve/vágva (vagy kitöltve ha szükséges, de itt egyszerűen vágjuk/méretezzük a config szerint)
     */
    public static function saveSimpleImage(UploadedFile $photo, string $folder, string $name, ?int $width = null, ?int $height = null, bool $whiteBackground = false): string
    {
        $image = InterventionImage::make($photo->getRealPath())->orientate();

        // Ha mindkettő null, nem méretezünk át
        if ($width || $height) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Aktuális méretek lekérése (átméretezés után)
        $currentWidth = $image->width();
        $currentHeight = $image->height();

        // Canvas mérete: ha meg van adva fix méret, azt használjuk, különben a kép méretét
        $canvasWidth = $width ?? $currentWidth;
        $canvasHeight = $height ?? $currentHeight;

        if ($whiteBackground) {
            $canvas = InterventionImage::canvas($canvasWidth, $canvasHeight, '#ffffff');
            $canvas->insert($image, 'center');
            $image = $canvas;
        } else {
            // Csak akkor kell canvas, ha van fix kért méret, ami nagyobb mint a kép (hogy legyen padding)
            // vagy ha transzparens hátteret akarunk a kért mérethez.
            // Ha nincs megadva valamelyik méret, akkor a kép mérete lesz a mérvadó.
            if ($width || $height) {
                $canvas = InterventionImage::canvas($canvasWidth, $canvasHeight);
                $canvas->insert($image, 'center');
                $image = $canvas;
            }
        }

        $imageUrl = $folder . '/' . $name . '-' . getRandNumber() . '.webp';
        $image->encode('webp', 80);
        Storage::disk('public')->put($imageUrl, (string) $image);

        return getImgStorage() . '/storage/' . $imageUrl;
    }
}
