<?php
namespace Weboldalnet\WebshopAiDefault\Mail;

use App\Models\ContactSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopContentService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopLegalInfoService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopWithdrawalService;

class WebshopOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $customContent;
    public $showPrices;

    /**
     * Create a new message instance.
     *
     * @param WebshopOrder $order
     */
    public function __construct(WebshopOrder $order)
    {
        $this->order = $order;
        $this->customContent = WebshopContentService::getContent('email', $order);
        $this->showPrices = WebshopSettingsService::getBool('site_product_prices_visible', true);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->customContent && $this->customContent->title ? $this->customContent->title : ($this->order->type === WebshopOrder::TYPE_QUOTE ? 'Árajánlatkérés visszaigazolása' : 'Rendelés visszaigazolása');

        $settings = ContactSettings::find(1);
        $contactData = $settings->contact_data;

        // Jogi tájékoztatás (csak valódi rendelésnél – árajánlatkérés nem szerződés):
        // a szerződést tartós adathordozón kell visszaigazolni az eladó adataival, az ÁSZF-fel
        // és az elállási tájékoztatással, nyilatkozatmintával együtt (45/2014. Korm. rendelet).
        $isOrder = $this->order->type !== WebshopOrder::TYPE_QUOTE;

        $mail = $this->from(config('app.shop_email'), config('app.shop_name'))
            ->subject($subject)
            ->view('mail.order-mail')
            ->with([
                'order' => $this->order,
                'customContent' => $this->customContent,
                'showPrices' => $this->showPrices,
                'contactData' => $contactData,
                'isOrder' => $isOrder,
                'seller' => WebshopLegalInfoService::seller(),
                'termsUrl' => WebshopLegalInfoService::termsUrl(),
                'privacyUrl' => WebshopLegalInfoService::privacyUrl(),
                // Lejárat nélküli, aláírt link: aki a levelet megkapta, a rendelés gazdája.
                'withdrawalUrl' => $isOrder && \Illuminate\Support\Facades\Route::has('site.webshop.withdrawals.create')
                    ? WebshopWithdrawalService::formUrl($this->order, null)
                    : null,
                'termsAttached' => false,
            ]);

        // Az ÁSZF PDF-ként csatolva: egy link nem tartós adathordozó, a melléklet igen.
        $termsPdf = $isOrder && WebshopLegalInfoService::attachTermsToOrderMail()
            ? WebshopLegalInfoService::termsLocalPdf()
            : null;

        if ($termsPdf) {
            $mail->attach($termsPdf, ['as' => 'Altalanos-Szerzodesi-Feltetelek.pdf', 'mime' => 'application/pdf'])
                ->with('termsAttached', true);
        }

        return $mail;
    }
}
