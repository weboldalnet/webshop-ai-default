<?php

use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductLabelController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductReviewController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopOrderController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopSettingController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCategoryController as SiteCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopProductController as SiteProductController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCartController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCompareController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCheckoutController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopReviewController;

Route::middleware('web')->group(function () {
    // Site oldali route-ok
    Route::domain(getSiteDomain())
        ->middleware('site_share')
        ->prefix('webshop')
        ->name('site.webshop.')
        ->group(function () {
            Route::get('/', [SiteCategoryController::class, 'index'])->name('categories.index');
            Route::get('/kategoria/{categorySlug}/products', [SiteCategoryController::class, 'products'])->name('categories.products')->where('categorySlug', '.*');
            Route::get('/kategoria/{categorySlug}', [SiteCategoryController::class, 'show'])->name('categories.show')->where('categorySlug', '.*');
            Route::get('/termek/{product:slug}', [SiteProductController::class, 'show'])->name('products.show');

            // Kosár
            Route::post('/cart/add', [WebshopCartController::class, 'add'])->name('cart.add');
            Route::get('/cart/dropdown', [WebshopCartController::class, 'dropdown'])->name('cart.dropdown');
            Route::post('/cart/update', [WebshopCartController::class, 'update'])->name('cart.update');
            Route::delete('/cart/remove', [WebshopCartController::class, 'remove'])->name('cart.remove');

            // Összehasonlítás
            Route::post('/compare/add', [WebshopCompareController::class, 'add'])->name('compare.add');
            Route::get('/compare/dropdown', [WebshopCompareController::class, 'dropdown'])->name('compare.dropdown');
            Route::delete('/compare/remove', [WebshopCompareController::class, 'remove'])->name('compare.remove');
            Route::get('/compare', [WebshopCompareController::class, 'index'])->name('compare.index');

            // Checkout
            Route::get('/checkout', [WebshopCheckoutController::class, 'index'])->name('checkout.index');
            Route::post('/checkout', [WebshopCheckoutController::class, 'store'])->name('checkout.store');
            Route::get('/checkout/success/{order}', [WebshopCheckoutController::class, 'success'])->name('checkout.success');

            // Payment result és retry
            Route::get('/payment/{order}/result', [WebshopCheckoutController::class, 'paymentResult'])->name('payment.result');
            Route::post('/payment/{order}/retry', [WebshopCheckoutController::class, 'retryPayment'])->name('payment.retry');

            // Vélemények
            Route::post('/reviews', [WebshopReviewController::class, 'store'])->name('reviews.store');
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
            Route::get('/products/search', [WebshopProductController::class, 'search'])->name('products.search');
            Route::get('/products', [WebshopProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [WebshopProductController::class, 'create'])->name('products.create');
            Route::post('/products', [WebshopProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [WebshopProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [WebshopProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [WebshopProductController::class, 'destroy'])->name('products.destroy');
            Route::post('/products/toggle-active', [WebshopProductController::class, 'toggleActive'])->name('products.toggle-active');
            Route::post('/products/sort', [WebshopProductController::class, 'sort'])->name('products.sort');

            // Termék vélemények
            Route::get('/products/reviews', [WebshopProductReviewController::class, 'allReviews'])->name('products.all-reviews');
            Route::get('/products/{product}/reviews', [WebshopProductReviewController::class, 'index'])->name('products.reviews.index');
            Route::delete('/products/{product}/reviews/{review}', [WebshopProductReviewController::class, 'destroy'])->name('products.reviews.destroy');
            Route::post('/products/{product}/reviews/toggle-active', [WebshopProductReviewController::class, 'toggleActive'])->name('products.reviews.toggle-active');

            // Termék galéria
            Route::post('/products/{product}/gallery', [WebshopProductController::class, 'storeGalleryImage'])->name('products.gallery.store');
            Route::delete('/products/{product}/gallery/{image}', [WebshopProductController::class, 'destroyGalleryImage'])->name('products.gallery.destroy');
            Route::post('/products/gallery/sort', [WebshopProductController::class, 'sortGallery'])->name('products.gallery.sort');
            Route::post('/products/gallery/toggle-active', [WebshopProductController::class, 'toggleGalleryActive'])->name('products.gallery.toggle-active');
            Route::post('/products/gallery/update-alt', [WebshopProductController::class, 'updateGalleryAlt'])->name('products.gallery.update-alt');

            // Termék dokumentumok
            Route::post('/products/{product}/documents', [WebshopProductController::class, 'storeDocument'])->name('products.documents.store');
            Route::delete('/products/{product}/documents/{document}', [WebshopProductController::class, 'destroyDocument'])->name('products.documents.destroy');
            Route::post('/products/documents/sort', [WebshopProductController::class, 'sortDocuments'])->name('products.documents.sort');
            Route::post('/products/documents/toggle-active', [WebshopProductController::class, 'toggleDocumentActive'])->name('products.documents.toggle-active');

            // Termék címkék
            Route::get('/labels', [WebshopProductLabelController::class, 'index'])->name('labels.index');
            Route::get('/labels/create', [WebshopProductLabelController::class, 'create'])->name('labels.create');
            Route::post('/labels', [WebshopProductLabelController::class, 'store'])->name('labels.store');
            Route::get('/labels/{label}/edit', [WebshopProductLabelController::class, 'edit'])->name('labels.edit');
            Route::put('/labels/{label}', [WebshopProductLabelController::class, 'update'])->name('labels.update');
            Route::delete('/labels/{label}', [WebshopProductLabelController::class, 'destroy'])->name('labels.destroy');

            // Rendelések
            Route::get('/orders', [WebshopOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [WebshopOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [WebshopOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}/details', [WebshopOrderController::class, 'details'])->name('orders.details');
            Route::get('/orders/{order}/edit', [WebshopOrderController::class, 'edit'])->name('orders.edit');
            Route::put('/orders/{order}', [WebshopOrderController::class, 'update'])->name('orders.update');
            Route::patch('/orders/{order}/status', [WebshopOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::delete('/orders/{order}', [WebshopOrderController::class, 'destroy'])->name('orders.destroy');
            Route::post('/orders/toggle-completed', [WebshopOrderController::class, 'toggleCompleted'])->name('orders.toggle-completed');
            Route::patch('/orders/{order}/mark-paid', [WebshopOrderController::class, 'markPaid'])->name('orders.mark-paid');
            Route::post('/orders/{order}/create-invoice', [WebshopOrderController::class, 'createInvoice'])->name('orders.create-invoice');
            Route::post('/orders/{order}/create-shipment', [WebshopOrderController::class, 'createShipment'])->name('orders.create-shipment');

            // Beállítások
            Route::get('/settings', [WebshopSettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [WebshopSettingController::class, 'update'])->name('settings.update');

            // Extra Beállítások (Webshop beállítások menüpont)
            Route::prefix('extra-settings')->name('extra-settings.')->group(function () {
                Route::get('/', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'index'])->name('index');

                // Email és Köszönjük oldal szerkesztése
                Route::get('/custom-contents', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'customContents'])->name('custom-contents.index');
                Route::post('/custom-contents', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'storeCustomContent'])->name('custom-contents.store');

                // Checkout Dokumentumok
                Route::get('/documents', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'documents'])->name('documents.index');
                Route::post('/documents', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'storeDocument'])->name('documents.store');

                // Mérési scriptek
                Route::get('/scripts', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'scripts'])->name('scripts.index');
                Route::post('/scripts', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'storeScript'])->name('scripts.store');
                Route::put('/scripts/{script}', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'updateScript'])->name('scripts.update');
                Route::delete('/scripts/{script}', [\Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController::class, 'destroyScript'])->name('scripts.destroy');
            });
        });
});
