<?php
namespace Weboldalnet\WebshopAiDefault\Mail;

use App\Models\ContactSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopContentService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

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
        $this->showPrices = WebshopSettingsService::getBool('site_show_prices', true);
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

        return $this->from(config('app.shop_email'), config('app.shop_name'))
            ->subject($subject)
            ->view('mail.order-mail')
            ->with([
                'order' => $this->order,
                'customContent' => $this->customContent,
                'showPrices' => $this->showPrices,
                'contactData' => $contactData,
            ]);
    }
}
