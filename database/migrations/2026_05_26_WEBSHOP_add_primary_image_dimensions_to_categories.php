<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $defaultWidth = config('webshop.primary_image.width', 500);
        $defaultHeight = config('webshop.primary_image.height', 500);

        DB::statement("ALTER TABLE public.webshop_categories ADD COLUMN primary_image_width INT DEFAULT $defaultWidth");
        DB::statement("ALTER TABLE public.webshop_categories ADD COLUMN primary_image_height INT DEFAULT $defaultHeight");
    }

    public function down()
    {
        DB::statement("ALTER TABLE public.webshop_categories DROP COLUMN primary_image_width");
        DB::statement("ALTER TABLE public.webshop_categories DROP COLUMN primary_image_height");
    }
};
