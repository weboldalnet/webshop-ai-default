<?php

namespace Weboldalnet\WebshopAiDefault\Mail;

use App\Models\ContactSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Weboldalnet\WebshopAiDefault\Models\WebshopWithdrawal;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * Elállási nyilatkozat visszaigazolása (tartós adathordozón, dátummal és időponttal).
 *
 * Ugyanaz a levél megy a vásárlónak és a webshop címére is – utóbbinál más a
 * tárgy, hogy a beérkező kérelem azonnal felismerhető legyen.
 */
class WebshopWithdrawalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $withdrawal;
    public $isShopCopy;
    public $showPrices;

    public function __construct(WebshopWithdrawal $withdrawal, bool $isShopCopy = false)
    {
        $this->withdrawal = $withdrawal;
        $this->isShopCopy = $isShopCopy;
        $this->showPrices = WebshopSettingsService::getBool('site_product_prices_visible', true);
    }

    public function build()
    {
        $subject = $this->isShopCopy
            ? __('webshop::mail.withdrawal.subject_shop', ['number' => $this->withdrawal->order_number])
            : __('webshop::mail.withdrawal.subject', ['number' => $this->withdrawal->order_number]);

        $contactData = [];
        try {
            $settings = ContactSettings::find(1);
            $contactData = $settings ? $settings->contact_data : [];
        } catch (\Throwable $e) {
            // A kapcsolati adatok hiánya ne akadályozza meg a levél kiküldését.
        }

        return $this->from(config('app.shop_email'), config('app.shop_name'))
            ->subject($subject)
            ->view('mail.withdrawal-mail')
            ->with([
                'withdrawal' => $this->withdrawal,
                'isShopCopy' => $this->isShopCopy,
                'showPrices' => $this->showPrices,
                'contactData' => $contactData,
            ]);
    }
}
