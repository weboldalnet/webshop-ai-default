<?php

namespace Weboldalnet\WebshopAiDefault\Mail;

use Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl;
use App\Models\ContactSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * Értesítés a vásárlónak: a rendelését lemondtuk, mert a határidőn belül nem érkezett meg az online fizetés
 * (webshop:cancel-unpaid-orders). A webshop többi levelével azonos keretben (mail/layouts/mail_layout).
 */
class WebshopOrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $hours;

    public function __construct(WebshopOrder $order, ?int $hours = null)
    {
        $this->order = $order;
        $this->hours = $hours ?? (int) config('webshop.unpaid_order_cancel_hours', 24);
    }

    public function build()
    {
        $contactData = optional(ContactSettings::find(1))->contact_data ?? [];

        return $this->from(config('app.shop_email'), config('app.shop_name'))
            ->subject(__('webshop::mail.cancelled.subject', ['number' => $this->order->order_number]))
            ->view('mail.order-cancelled-mail')
            ->with([
                'order' => $this->order,
                'hours' => $this->hours,
                'showPrices' => WebshopSettingsService::getBool('site_product_prices_visible', true),
                'contactData' => $contactData,
                'shopUrl' => \Illuminate\Support\Facades\Route::has('site.webshop.categories.index')
                    ? WebshopUrl::route('site.webshop.categories.index')
                    : url('/'),
                'buttonColor' => $this->brandColor(),
            ]);
    }

    /** A gomb a projekt márkaszínét kapja (core ContentHelper::THEME), annak hiányában sötétszürke. */
    private function brandColor(): string
    {
        // class_exists előbb: a defined() magától nem tölti be az osztályt
        $theme = class_exists(\App\Helpers\ContentHelper::class) && defined(\App\Helpers\ContentHelper::class . '::THEME')
            ? \App\Helpers\ContentHelper::THEME
            : [];
        $color = (string) ($theme['theme_color'] ?? '');

        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#333333';
    }
}
