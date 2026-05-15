<?php

use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopOrderController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopSettingController;

Route::middleware('web')->group(function () {
    // Site oldali route-ok (előkészítés, még nincs implementáció)
    Route::domain(getSiteDomain())
        ->middleware('site_share')
        ->group(function () {
            // Site webshop route-ok ide kerülnek később
        });

    // Admin oldali route-ok
    Route::prefix('webshop')
        ->domain(getAdminDomain())
        ->middleware(['admin_share', 'auth:admin'])
        ->name('admin.webshop.')
        ->group(function () {

            // Tulajdonság kategóriák
            Route::get('/property-categories', [WebshopPropertyCategoryController::class, 'index'])->name('property-categories.index');
            Route::get('/property-categories/create', [WebshopPropertyCategoryController::class, 'create'])->name('property-categories.create');
            Route::post('/property-categories', [WebshopPropertyCategoryController::class, 'store'])->name('property-categories.store');
            Route::get('/property-categories/{propertyCategory}/edit', [WebshopPropertyCategoryController::class, 'edit'])->name('property-categories.edit');
            Route::put('/property-categories/{propertyCategory}', [WebshopPropertyCategoryController::class, 'update'])->name('property-categories.update');
            Route::delete('/property-categories/{propertyCategory}', [WebshopPropertyCategoryController::class, 'destroy'])->name('property-categories.destroy');
            Route::post('/property-categories/toggle-active', [WebshopPropertyCategoryController::class, 'toggleActive'])->name('property-categories.toggle-active');
            Route::post('/property-categories/sort', [WebshopPropertyCategoryController::class, 'sort'])->name('property-categories.sort');

            // Tulajdonságok
            Route::get('/property-categories/{propertyCategory}/properties', [WebshopPropertyController::class, 'index'])->name('properties.index');
            Route::get('/property-categories/{propertyCategory}/properties/create', [WebshopPropertyController::class, 'create'])->name('properties.create');
            Route::post('/property-categories/{propertyCategory}/properties', [WebshopPropertyController::class, 'store'])->name('properties.store');
            Route::get('/property-categories/{propertyCategory}/properties/{property}/edit', [WebshopPropertyController::class, 'edit'])->name('properties.edit');
            Route::put('/property-categories/{propertyCategory}/properties/{property}', [WebshopPropertyController::class, 'update'])->name('properties.update');
            Route::delete('/property-categories/{propertyCategory}/properties/{property}', [WebshopPropertyController::class, 'destroy'])->name('properties.destroy');
            Route::post('/properties/toggle-active', [WebshopPropertyController::class, 'toggleActive'])->name('properties.toggle-active');
            Route::post('/properties/sort', [WebshopPropertyController::class, 'sort'])->name('properties.sort');

            // Kategóriák
            Route::get('/categories', [WebshopCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [WebshopCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [WebshopCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [WebshopCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [WebshopCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [WebshopCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/toggle-active', [WebshopCategoryController::class, 'toggleActive'])->name('categories.toggle-active');
            Route::post('/categories/sort', [WebshopCategoryController::class, 'sort'])->name('categories.sort');

            // Termékek
            Route::get('/products', [WebshopProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [WebshopProductController::class, 'create'])->name('products.create');
            Route::post('/products', [WebshopProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [WebshopProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [WebshopProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [WebshopProductController::class, 'destroy'])->name('products.destroy');
            Route::post('/products/toggle-active', [WebshopProductController::class, 'toggleActive'])->name('products.toggle-active');
            Route::post('/products/sort', [WebshopProductController::class, 'sort'])->name('products.sort');

            // Termék galéria
            Route::post('/products/{product}/gallery', [WebshopProductController::class, 'storeGalleryImage'])->name('products.gallery.store');
            Route::delete('/products/{product}/gallery/{image}', [WebshopProductController::class, 'destroyGalleryImage'])->name('products.gallery.destroy');
            Route::post('/products/gallery/sort', [WebshopProductController::class, 'sortGallery'])->name('products.gallery.sort');
            Route::post('/products/gallery/toggle-active', [WebshopProductController::class, 'toggleGalleryActive'])->name('products.gallery.toggle-active');

            // Rendelések
            Route::get('/orders', [WebshopOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}/edit', [WebshopOrderController::class, 'edit'])->name('orders.edit');
            Route::put('/orders/{order}', [WebshopOrderController::class, 'update'])->name('orders.update');
            Route::delete('/orders/{order}', [WebshopOrderController::class, 'destroy'])->name('orders.destroy');
            Route::post('/orders/toggle-completed', [WebshopOrderController::class, 'toggleCompleted'])->name('orders.toggle-completed');

            // Beállítások
            Route::get('/settings', [WebshopSettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [WebshopSettingController::class, 'update'])->name('settings.update');
        });
});
