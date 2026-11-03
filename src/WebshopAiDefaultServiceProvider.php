<?php

namespace Weboldalnet\WebshopAiDefault;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCompareService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Console\Commands\CancelUnpaidOrders;
use Weboldalnet\WebshopAiDefault\Console\Commands\GenerateMerchantFeeds;
use Weboldalnet\WebshopAiDefault\Support\PackageHelper;
use Weboldalnet\WebshopAiDefault\Console\ExtendViewsArticlesCommand;
use Weboldalnet\WebshopAiDefault\Console\InstallArticlesCommand;
use Weboldalnet\WebshopAiDefault\Listeners\Commerce\HandlePaymentSucceeded;
use Weboldalnet\WebshopAiDefault\Listeners\Commerce\HandlePaymentFailed;
use Weboldalnet\WebshopAiDefault\Listeners\Commerce\HandlePaymentCancelled;

class WebshopAiDefaultServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Commerce-core event listenerek regisztrálása, ha a commerce-core elérhető
        if (class_exists(\Weboldalnet\CommerceCore\Events\PaymentSucceeded::class)) {
            Event::listen(\Weboldalnet\CommerceCore\Events\PaymentSucceeded::class, HandlePaymentSucceeded::class);
        }
        if (class_exists(\Weboldalnet\CommerceCore\Events\PaymentFailed::class)) {
            Event::listen(\Weboldalnet\CommerceCore\Events\PaymentFailed::class, HandlePaymentFailed::class);
        }
        if (class_exists(\Weboldalnet\CommerceCore\Events\PaymentCancelled::class)) {
            Event::listen(\Weboldalnet\CommerceCore\Events\PaymentCancelled::class, HandlePaymentCancelled::class);
        }

        $this->registerBuiltinShippingProviders();

        $this->registerSchedule();

        // Vásárlói fiókok (weboldalnet/commerce-users): a kapcsolók a Webshop beállításokból,
        // a fiókmenübe a Rendeléseim. Telepített modul nélkül nem csinál semmit.
        \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUserService::registerWithModule();

        // Route-ok betöltése
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // View-k betöltése - gyökér szintű hozzáadás, hogy `view('admin.webshop...')` működjön
        // A fő projekt resources/views felülírja, ha ott is létezik a fájl
        $this->app['view']->addLocation(__DIR__.'/../resources/views');

        // Package prefixszel is elérhető view-k (webshop::...)
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'webshop');

        // Korábbi settings/views kezelés
        $this->loadViewsFrom(__DIR__.'/../settings/views', PackageHelper::PACKAGE_PREFIX);

        // Felületi szövegek: __('webshop::checkout.…'). A projekt felülírhatja: lang/vendor/webshop/{locale}/*.php
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'webshop');
        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/webshop'),
        ], PackageHelper::PACKAGE_PREFIX . '-lang');

        // Nyelvfüggő ár- és dátumformázás a nézetekben: {{ WsFormat::money($x) }}
        \Illuminate\Foundation\AliasLoader::getInstance()->alias('WsFormat', \Weboldalnet\WebshopAiDefault\Helpers\WebshopHelper::class);
        // Nyelvkövető címek: {{ WsUrl::route('site.webshop.checkout.index') }}, WsUrl::product($p), WsUrl::category($c)
        \Illuminate\Foundation\AliasLoader::getInstance()->alias('WsUrl', \Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl::class);

        // Migrációk betöltése
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Config betöltése és publikálása
        $this->publishes([
            __DIR__.'/../config/webshop.php' => config_path('webshop.php'),
        ], PackageHelper::PACKAGE_PREFIX . '-config');

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
            if (\Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl::isWebshopRequest()) {
                // A gyerek szinteket UGYANÚGY szűrni kell, mint a gyökeret.
                // Enélkül egy inaktív vagy a menüsorból kikapcsolt alkategória is
                // megjelenne a lenyílóban – az adminban kikapcsolnád, és mégis kint
                // maradna. A closure a rendezést is szintenként elvégzi.
                $wsMenuFilter = function ($query) {
                    $query->where('is_active', true)
                        ->where('show_in_sticky_header', true)
                        ->orderBy('sort_order');
                };

                $view->with('stickyCategories', WebshopCategory::active()->stickyHeader()->ordered()
                    ->with(['children' => function ($query) use ($wsMenuFilter) {
                        $wsMenuFilter($query);
                        $query->with(['children' => $wsMenuFilter]);
                    }])
                    ->get());
                $view->with('cartCount', WebshopCartService::getCount());
                $view->with('compareCount', WebshopCompareService::getCount());
                $view->with('ws', WebshopSettingsService::all());
            }
        });
    }

    /**
     * Ütemezett feladatok a Laravel ütemezőjében — a szerveren a szokásos `* * * * * php artisan schedule:run` cron kell,
     * külön cron-sor nem. Mindkettő kikapcsolható a config/.env-ből (webshop.schedule.*: üres = kikapcsolva).
     *
     * - Merchant feed (Google / Facebook XML): a fájl magától nem frissül, és az elavult ár / készlet miatt a Merchant
     *   Center letilthatja a terméket. Csak bekapcsolt merchant feed beállításnál fut.
     * - Félbehagyott online fizetésű rendelések lemondása (webshop:cancel-unpaid-orders): a lefoglalt készlet
     *   felszabadítása, a vásárló értesítése.
     */
    protected function registerSchedule(): void
    {
        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, function ($schedule) {
            $frequency = function ($event, $value, string $fallback) {
                // metódusnév (daily, hourly …) vagy cron-kifejezés (15 3 * * *)
                if (str_contains((string) $value, ' ')) {
                    return $event->cron((string) $value);
                }

                return method_exists($event, (string) $value) ? $event->{$value}() : $event->{$fallback}();
            };

            if ($feeds = config('webshop.schedule.merchant_feeds')) {
                $frequency(
                    $schedule->command('webshop:generate-merchant-feeds')
                        ->withoutOverlapping(60)
                        ->when(fn () => WebshopSettingsService::getBool('category_merchant_feed_enabled')),
                    $feeds,
                    'daily'
                );
            }

            if (($cancel = config('webshop.schedule.cancel_unpaid_orders')) && (int) config('webshop.unpaid_order_cancel_hours', 24) > 0) {
                $frequency(
                    $schedule->command('webshop:cancel-unpaid-orders')->withoutOverlapping(30),
                    $cancel,
                    'hourly'
                );
            }
        });
    }

    /**
     * A webshop saját, beépített szállítási módjai (külön csomag nélkül).
     *
     * MPL futárszolgálat: nincs API-kapcsolat – a pénztárban házhoz szállításként,
     * szállítási címmel választható, a szállítmányt pedig az ügyfélszolgálat kézzel
     * rögzíti az MPL rendszerében. A díjat az adminban adják meg (mint a saját
     * futárszolgálatnál), a pénztárban a webshop kapcsolója dönti el, hogy
     * megjelenik-e – alapból KI van kapcsolva, így egy frissítés után sem bukkan
     * fel magától egyetlen bolt pénztárában sem.
     *
     * Adatbázist itt nem olvasunk (friss telepítésnél a migráció előtt még nincs
     * beállítás-tábla): a provider a díjat csak számoláskor kérdezi le.
     */
    protected function registerBuiltinShippingProviders(): void
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Managers\ShippingManager::class)
            || !interface_exists(\Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface::class)) {
            return;
        }

        try {
            $this->app->make(\Weboldalnet\CommerceCore\Managers\ShippingManager::class)->register(
                'mpl',
                new \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\Shipping\ManualCourierShippingProvider(
                    'mpl',
                    'webshop::order.shipping_method.mpl',
                    'https://www.posta.hu/nyomkovetes/nyitooldal?searchvalue={code}'
                )
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Webshop: az MPL szállítási mód regisztrálása nem sikerült: ' . $e->getMessage());
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webshop.php', 'webshop');

        $this->commands([
            InstallArticlesCommand::class,
            GenerateMerchantFeeds::class,
            CancelUnpaidOrders::class,
        ]);

        $this->commands([
            ExtendViewsArticlesCommand::class,
        ]);
    }
}
