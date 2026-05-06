<?php

use App\Helpers\CustomPageHelper;

use App\Http\Controllers\Admin\Article\ArticleController;

Route::middleware('web')->group(function () {
    // Site oldali route-ok
    Route::namespace('Weboldalnet\WebshopAiDefault\Http\Controllers\Site')
        ->domain(getSiteDomain())
        ->middleware('site_share')
        ->group(function () {
            // Auth flow
        });

    // Admin oldali route-ok
    Route::prefix('webshop')
        ->namespace('Weboldalnet\WebshopAiDefault\Http\Controllers\Admin')
        ->domain(getAdminDomain())
        ->middleware('admin_share')
        ->group(function () {
            Route::middleware(['web', 'auth:admin'])->group(function () {

            });
        });
});