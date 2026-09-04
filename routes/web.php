<?php

use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopPropertyController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductLabelController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopProductReviewController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopOrderController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopSettingController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopShipmentController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopExtraSettingController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopHomePageController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCategoryController as SiteCategoryController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopProductController as SiteProductController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCartController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCompareController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopCheckoutController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopReviewController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop\WebshopWithdrawalController as SiteWithdrawalController;
use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopWithdrawalController as AdminWithdrawalController;

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
            // A fizetési eredmény oldal ezt kérdezgeti, amíg a fizetés függőben van
            Route::get('/payment/{order}/status', [WebshopCheckoutController::class, 'paymentStatus'])->name('payment.status');

            // Vélemények
            Route::post('/reviews', [WebshopReviewController::class, 'store'])->name('reviews.store');

            // Elállás
            // A rendelésszám itt azonosítóként működik, ezért a keresést
            // korlátozzuk, hogy ne lehessen rendelésszámokat próbálgatni.
            Route::post('/elallas/kereses', [SiteWithdrawalController::class, 'lookup'])
                ->middleware('throttle:10,1')
                ->name('withdrawals.lookup');
            Route::get('/elallas/{orderNumber}', [SiteWithdrawalController::class, 'create'])->name('withdrawals.create');
            Route::post('/elallas/{orderNumber}', [SiteWithdrawalController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('withdrawals.store');
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

            // Elállási kérelmek
            Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::get('/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('withdrawals.show');
            Route::put('/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'update'])->name('withdrawals.update');
            Route::delete('/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'destroy'])->name('withdrawals.destroy');

            // Szállítmányok (provider-független lista)
            Route::get('/shipments', [WebshopShipmentController::class, 'index'])->name('shipments.index');
            Route::get('/shipments/{id}/label', [WebshopShipmentController::class, 'downloadLabel'])->name('shipments.label');
            Route::post('/shipments/{id}/retry', [WebshopShipmentController::class, 'retry'])->name('shipments.retry');

            // Beállítások
            Route::get('/settings', [WebshopSettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [WebshopSettingController::class, 'update'])->name('settings.update');

            // Extra Beállítások (Webshop beállítások menüpont)
            Route::prefix('extra-settings')->name('extra-settings.')->group(function () {
                Route::get('/', [WebshopExtraSettingController::class, 'index'])->name('index');

                // Email és Köszönjük oldal szerkesztése
                Route::get('/custom-contents', [WebshopExtraSettingController::class, 'customContents'])->name('custom-contents.index');
                Route::post('/custom-contents', [WebshopExtraSettingController::class, 'storeCustomContent'])->name('custom-contents.store');

                // Checkout Dokumentumok
                Route::get('/documents', [WebshopExtraSettingController::class, 'documents'])->name('documents.index');
                Route::post('/documents', [WebshopExtraSettingController::class, 'storeDocument'])->name('documents.store');

                // Webshop nyitóoldal (blokkokból épített kezdőoldal)
                Route::get('/home-page', [WebshopHomePageController::class, 'index'])->name('home-page.index');
                Route::post('/home-page', [WebshopHomePageController::class, 'storeSettings'])->name('home-page.store');
                // A FIX szegmensű útvonalak a {block} paraméteres elé kell kerüljenek
                Route::post('/home-page/blocks/sort', [WebshopHomePageController::class, 'sortBlocks'])->name('home-page.blocks.sort');
                Route::post('/home-page/blocks/toggle-active', [WebshopHomePageController::class, 'toggleBlockActive'])->name('home-page.blocks.toggle-active');
                Route::post('/home-page/blocks', [WebshopHomePageController::class, 'storeBlock'])->name('home-page.blocks.store');
                Route::put('/home-page/blocks/{block}', [WebshopHomePageController::class, 'updateBlock'])->name('home-page.blocks.update');
                Route::delete('/home-page/blocks/{block}', [WebshopHomePageController::class, 'destroyBlock'])->name('home-page.blocks.destroy');
                // Pénztár kiegészítések (kérdőív + értesítő dobozok)
                Route::get('/checkout-extras', [WebshopExtraSettingController::class, 'checkoutExtras'])->name('checkout-extras.index');
                Route::post('/checkout-extras', [WebshopExtraSettingController::class, 'storeCheckoutExtras'])->name('checkout-extras.store');

                // Mérési scriptek
                Route::get('/scripts', [WebshopExtraSettingController::class, 'scripts'])->name('scripts.index');
                Route::post('/scripts', [WebshopExtraSettingController::class, 'storeScript'])->name('scripts.store');
                Route::put('/scripts/{script}', [WebshopExtraSettingController::class, 'updateScript'])->name('scripts.update');
                Route::delete('/scripts/{script}', [WebshopExtraSettingController::class, 'destroyScript'])->name('scripts.destroy');
            });
        });
});
