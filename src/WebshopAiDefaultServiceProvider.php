<?php

namespace Weboldalnet\WebshopAiDefault;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCompareService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Support\PackageHelper;
use Weboldalnet\WebshopAiDefault\Console\ExtendViewsArticlesCommand;
use Weboldalnet\WebshopAiDefault\Console\InstallArticlesCommand;

class WebshopAiDefaultServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Route-ok betöltése
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // View-k betöltése - gyökér szintű hozzáadás, hogy `view('admin.webshop...')` működjön
        // A fő projekt resources/views felülírja, ha ott is létezik a fájl
        $this->app['view']->addLocation(__DIR__.'/../resources/views');

        // Package prefixszel is elérhető view-k (webshop::...)
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webshop');

        // Korábbi settings/views kezelés
        $this->loadViewsFrom(__DIR__.'/../settings/views', PackageHelper::PACKAGE_PREFIX);

        // Migrációk betöltése
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publisholható elemek (PackageHelper-ből)
        $publishList = [];
        foreach (PackageHelper::PACKAGE_LIST as $name => $publish) {
            $this->publishes([
                $publish['source'] => base_path($publish['destination']),
            ], PackageHelper::PACKAGE_PREFIX . '-' . $name);

            $publishList[$publish['source']] = base_path($publish['destination']);
        }
        $this->publishes($publishList, PackageHelper::PACKAGE_PREFIX . '-all');

        // View-k publisholása a fő projektbe
        $this->publishes([
            __DIR__.'/../resources/views/admin/webshop' => resource_path('views/admin/webshop'),
        ], PackageHelper::PACKAGE_PREFIX . '-views');

        // Publikus assetek publisholása - szétbontva CSS és JS fájlokra
        $this->publishes([
            __DIR__.'/../public/packages/webshop' => public_path('packages/webshop'),
        ], PackageHelper::PACKAGE_PREFIX . '-assets');

        $this->publishes([
            __DIR__.'/../public/packages/webshop/admin/css' => public_path('packages/webshop/admin/css'),
            __DIR__.'/../public/packages/webshop/site/css' => public_path('packages/webshop/site/css'),
        ], PackageHelper::PACKAGE_PREFIX . '-assets-css');

        $this->publishes([
            __DIR__.'/../public/packages/webshop/admin/js' => public_path('packages/webshop/admin/js'),
            __DIR__.'/../public/packages/webshop/site/js' => public_path('packages/webshop/site/js'),
        ], PackageHelper::PACKAGE_PREFIX . '-assets-js');

        // View Composers a Site oldalhoz
        View::composer(['site.webshop.*', 'site.layouts.*'], function ($view) {
            if (request()->is('webshop*') || (request()->route() && str_starts_with(request()->route()->getName(), 'site.webshop.'))) {
                $view->with('stickyCategories', WebshopCategory::active()->stickyHeader()->ordered()->with('children.children')->get());
                $view->with('cartCount', WebshopCartService::getCount());
                $view->with('compareCount', WebshopCompareService::getCount());
                $view->with('ws', WebshopSettingsService::all());
            }
        });
    }

    public function register()
    {
        $this->commands([
            InstallArticlesCommand::class,
        ]);

        $this->commands([
            ExtendViewsArticlesCommand::class,
        ]);
    }
}
