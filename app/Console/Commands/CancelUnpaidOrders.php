<?php

namespace Weboldalnet\WebshopAiDefault\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;
use Weboldalnet\CommerceCore\Status\PaymentStatus;
use Weboldalnet\WebshopAiDefault\Mail\WebshopOrderCancelledMail;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopStockService;
use Weboldalnet\WebshopAiDefault\Support\WebshopConnection;

/**
 * Félbehagyott online fizetésű rendelések lemondása.
 *
 * A készlet a rendelés LEADÁSAKOR foglalódik le (WebshopStockService). Sikertelen vagy megszakított online fizetésnél a
 * rendelés szándékosan függőben marad, hogy a vásárló újrapróbálhassa — de ha soha nem fizet, a lefoglalt darab örökre
 * foglalt maradna. Ez a parancs a megadott idő (alapból 24 óra) után lemondja az ilyen rendelést, visszaadja a készletet,
 * és levélben értesíti a vásárlót.
 *
 * Csak ONLINE fizetési módú (Barion, SimplePay, Stripe …), még függő rendeléshez nyúl: utánvét, előre utalás és helyszíni
 * fizetés kimarad (az átutalás napokig tarthat). Nem mondja le, ha:
 *   - van a rendeléshez fizetett tranzakció (fizetve, csak az esemény nem ért célba — ezt az admin nézze meg);
 *   - a rendelés vagy az utolsó fizetési kísérlete frissebb a határidőnél (épp fizethet, vagy most próbálta újra).
 * Az elmaradt értesítéseket előtte a commerce-core fizetés-egyeztetése (commerce:reconcile-payments) pótolja.
 *
 * Ütemezve: webshop.schedule.cancel_unpaid_orders (alapból óránként); kézzel: php artisan webshop:cancel-unpaid-orders
 */
class CancelUnpaidOrders extends Command
{
    protected $signature = 'webshop:cancel-unpaid-orders
        {--hours= : ennyi óra után mond le (alapból webshop.unpaid_order_cancel_hours)}
        {--dry-run : csak kilistázza, mit mondana le}';

    protected $description = 'A határidőn belül ki nem fizetett online fizetésű rendelések lemondása, a készlet visszaadása és a vásárló értesítése';

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?: config('webshop.unpaid_order_cancel_hours', 24));
        if ($hours <= 0) {
            $this->info('A félbehagyott rendelések lemondása ki van kapcsolva (webshop.unpaid_order_cancel_hours = 0).');

            return self::SUCCESS;
        }

        // minden telepített online fizetési mód (a kikapcsoltak is: a rendelés még akkor jöhetett, amikor be volt kapcsolva)
        $onlineMethods = array_keys(array_filter(
            WebshopCommerceService::getAvailablePaymentProviders(),
            fn ($meta) => !empty($meta['online'])
        ));
        if (!$onlineMethods) {
            $this->info('Nincs online fizetési mód — nincs teendő.');

            return self::SUCCESS;
        }

        $cutoff = now()->subHours($hours);
        $orders = WebshopOrder::query()
            ->where('type', '!=', WebshopOrder::TYPE_QUOTE)
            ->where('status', WebshopOrder::STATUS_PENDING)
            ->whereIn('payment_method', $onlineMethods)
            ->whereIn('payment_status', [
                WebshopOrder::PAYMENT_STATUS_UNPAID,
                WebshopOrder::PAYMENT_STATUS_PENDING,
                WebshopOrder::PAYMENT_STATUS_FAILED,
                WebshopOrder::PAYMENT_STATUS_CANCELLED,
            ])
            ->where('created_at', '<=', $cutoff)
            ->where('updated_at', '<=', $cutoff)
            ->orderBy('id')
            ->limit(100)
            ->get();

        $cancelled = $skipped = 0;
        foreach ($orders as $order) {
            $transactions = PaymentTransaction::query()->where('order_id', $order->id)->get();

            if ($transactions->contains(fn ($tx) => $tx->status === PaymentStatus::PAID)) {
                $skipped++;
                Log::warning('Félbehagyott rendelés: van fizetett tranzakciója, de a rendelés nincs fizetettnek jelölve — nem mondtuk le, ellenőrizd.', ['order' => $order->order_number]);
                continue;
            }
            if ($transactions->contains(fn ($tx) => $tx->created_at && $tx->created_at->gt($cutoff))) {
                // az utolsó fizetési kísérlet frissebb a határidőnél: a fizetés még folyamatban lehet
                $skipped++;
                continue;
            }

            // Utolsó ellenőrzés a szolgáltatónál: egy régi függő tranzakció valójában kifizetett lehet (elveszett
            // értesítés, és az egyeztetés csak 2 napra néz vissza — pl. az első futáskor egy régóta működő boltban).
            // Ha nem tudjuk biztosan, hogy nincs fizetve, NEM mondjuk le.
            $state = $this->providerState($order, $transactions, (bool) $this->option('dry-run'));
            if ($state !== 'unpaid') {
                $skipped++;
                if ($state !== 'open') {
                    Log::warning('Félbehagyott rendelés: a szolgáltatói ellenőrzés miatt nem mondtuk le.', ['order' => $order->order_number, 'state' => $state]);
                }
                $this->line('Kihagyva: ' . $order->order_number . ' (szolgáltató: ' . $state . ')');
                continue;
            }

            // a régóta függő rendeléseket (pl. az első futáskor) csendben mondjuk le: hetekkel később ne kapjon levelet
            $notify = $order->created_at && $order->created_at->gte(now()->subHours(max($hours, (int) config('webshop.unpaid_order_mail_max_age_hours', 72))));

            if ($this->option('dry-run')) {
                $this->line('Lemondaná: ' . $order->order_number . ' (' . $order->created_at->format('Y. m. d. H:i') . ', ' . $order->payment_method . ', '
                    . $order->payment_status . ($notify ? ', levéllel' : ', levél nélkül') . ')');
                continue;
            }

            if ($this->cancel($order, $hours, $notify)) {
                $cancelled++;
                if ($notify) {
                    $this->sendMail($order);
                }
            }
        }

        $summary = $orders->count() . ' jelölt, ' . $cancelled . ' lemondva, ' . $skipped . ' kihagyva.';
        $this->info($summary);
        if ($cancelled || $skipped) {
            Log::info('webshop:cancel-unpaid-orders — ' . $summary);
        }

        return self::SUCCESS;
    }

    /**
     * A rendelés függő tranzakcióinak állapota a szolgáltatónál: 'unpaid' (lemondható), 'paid' (fizetve — az eredményt
     * fel is dolgozzuk, ahogy a commerce-core egyeztetése tenné), 'open' (a fizetés még folyamatban), 'error' (nem sikerült
     * lekérdezni). Kikapcsolt / nem lekérdezhető szolgáltatónál nincs mit kérdezni: lemondható. Próbafutásnál csak olvas.
     */
    private function providerState(WebshopOrder $order, $transactions, bool $readOnly): string
    {
        if (!class_exists(\Weboldalnet\CommerceCore\Managers\PaymentManager::class)) {
            return 'unpaid';
        }
        $payments = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);

        foreach ($transactions as $tx) {
            if (!in_array($tx->status, [PaymentStatus::PENDING, PaymentStatus::UNPAID], true)
                || !$payments->hasProvider($tx->provider)
                || !method_exists($provider = $payments->getProvider($tx->provider), 'queryStatus')) {
                continue;
            }
            try {
                $result = $provider->queryStatus($tx);
            } catch (\Throwable $e) {
                Log::warning('Félbehagyott rendelés: a fizetés állapota nem kérdezhető le.', ['order' => $order->order_number, 'message' => $e->getMessage()]);

                return 'error';
            }
            if (!$result || !$result->status) {
                continue;
            }
            if ($result->status === PaymentStatus::PENDING) {
                return 'open';
            }
            if (!$readOnly) {
                app(\Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor::class)
                    ->processResult($tx->provider, $result, ['source' => 'cancel-unpaid-orders'] + (array) ($result->rawPayload ?? []));
            }
            if ($result->status === PaymentStatus::PAID) {
                return 'paid';
            }
        }

        return $order->fresh()?->isPaid() ? 'paid' : 'unpaid';
    }

    /** Lemondás zárolt sorral (közben befuthatott egy fizetés vagy egy admin-módosítás), készlet-visszaadással. */
    private function cancel(WebshopOrder $order, int $hours, bool $notify): bool
    {
        return WebshopConnection::db()->transaction(function () use ($order, $hours, $notify) {
            $locked = WebshopOrder::whereKey($order->id)->lockForUpdate()->first();
            if (!$locked || $locked->status !== WebshopOrder::STATUS_PENDING || $locked->isPaid()) {
                return false;
            }

            $note = '[' . now()->format('Y. m. d. H:i') . '] Automatikusan lemondva: ' . $hours
                . ' órán belül nem érkezett meg az online fizetés. A készlet visszakerült'
                . ($notify ? ', a vásárló levelet kapott.' : '; régi rendelés, ezért levél nem ment a vásárlónak.');
            $locked->update([
                'status' => WebshopOrder::STATUS_CANCELLED,
                'is_completed' => false,
                'completed_at' => null,
                'admin_note' => trim(($locked->admin_note ? $locked->admin_note . "\n" : '') . $note),
            ]);

            $locked->load('items');
            WebshopStockService::restore($locked);

            $order->setRawAttributes($locked->getAttributes(), true);

            return true;
        });
    }

    /** A vásárló értesítése; a levél hibája nem akasztja meg a többi rendelés lemondását. */
    private function sendMail(WebshopOrder $order): void
    {
        if (!filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            // a rendelés nyelvén
            \Weboldalnet\WebshopAiDefault\Support\WebshopOrderLocale::mail($order->customer_email, new WebshopOrderCancelledMail($order->load('items')), \Weboldalnet\WebshopAiDefault\Support\WebshopOrderLocale::of($order));
        } catch (\Throwable $e) {
            Log::error('Félbehagyott rendelés: a lemondó levél nem ment ki.', ['order' => $order->order_number, 'message' => $e->getMessage()]);
        }
    }
}
