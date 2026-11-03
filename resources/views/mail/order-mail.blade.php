@extends('mail/layouts/mail_layout')

@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        {{ __('webshop::mail.greeting', ['name' => $order->customer_name]) }}
                    </span>
                </div>

                @if($customContent && $customContent->content)
                    <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                        {!! $customContent->content !!}
                    </div>
                @endif

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    {{ __('webshop::mail.order.details_title', ['number' => $order->order_number]) }}
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ __('webshop::common.product') }}</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">{{ __('webshop::mail.order.qty_short') }}</th>
                            @if($showPrices && $order->total_price > 0)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">{{ __('webshop::common.unit_price_label') }}</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">{{ __('webshop::common.total') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    {{ $item->product_name }}
                                    @if($item->product && $item->product->secondary_name)
                                        <br><small style="color: #777;">{{ $item->product->secondary_name }}</small>
                                    @endif
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $item->quantity }}</td>
                                @if($showPrices && $order->total_price > 0)
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($item->unit_price) }} {{ $order->currency }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($item->total_price) }} {{ $order->currency }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if($showPrices && $order->total_price > 0)
                        {{-- Fizetendő bontása. A tételek összege önmagában nem adja ki a
                             végösszeget (szállítás, kupon), ezért mindkettőt kiírjuk –
                             enélkül a levont kedvezmény nyomtalanul eltűnne a levélből. --}}
                        @php $wsBreakdown = $order->priceBreakdown(); @endphp
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ __('webshop::mail.order.items_total') }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($wsBreakdown['items']) }} {{ $order->currency }}</td>
                            </tr>

                            @if($order->shipping_method)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ __('webshop::mail.order.shipping_fee') }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        {{ $wsBreakdown['shipping'] > 0 ? WsFormat::number($wsBreakdown['shipping']) . ' ' . $order->currency : __('webshop::mail.order.free') }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBreakdown['discount'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        {{ $order->coupon_code ? __('webshop::mail.order.coupon_discount_code', ['code' => $order->coupon_code]) : __('webshop::mail.order.coupon_discount') }}
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right; color: #1e7e34;">
                                        -{{ WsFormat::number($wsBreakdown['discount']) }} {{ $order->currency }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBreakdown['cod'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ __('webshop::mail.order.cod_fee') }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($wsBreakdown['cod']) }} {{ $order->currency }}</td>
                                </tr>
                            @endif

                            @if($wsBreakdown['other'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ __('webshop::mail.order.other_fees') }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($wsBreakdown['other']) }} {{ $order->currency }}</td>
                                </tr>
                            @endif

                            <tr style="font-weight: bold; background-color: #f9f9f9;">
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ __('webshop::mail.order.grand_total') }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::number($order->total_price) }} {{ $order->currency }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                <div style="display: flex; flex-wrap: wrap; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 250px; margin-right: 20px; margin-bottom: 15px;">
                        <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">{{ __('webshop::mail.order.personal_title') }}</p>
                        <p style="margin: 0; font-size: 14px;">
                            <strong>{{ __('webshop::mail.order.name') }}</strong> {{ $order->customer_name }}<br>
                            <strong>{{ __('webshop::mail.order.email') }}</strong> {{ $order->customer_email }}<br>
                            @if($order->customer_phone) <strong>{{ __('webshop::mail.order.phone') }}</strong> {{ $order->customer_phone }}<br> @endif
                            @if($order->customer_company) <strong>{{ __('webshop::mail.order.company') }}</strong> {{ $order->customer_company }}<br> @endif
                            @if($order->customer_tax_number) <strong>{{ __('webshop::mail.order.tax_number') }}</strong> {{ $order->customer_tax_number }}<br> @endif
                        </p>
                    </div>

                    @php($mailBillingData = $order->getBillingDataArray())
                    @php($mailShippingData = $order->getShippingDataArray())

                    @if($mailBillingData)
                        <div style="flex: 1; min-width: 250px; margin-right: 20px; margin-bottom: 15px;">
                            <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">{{ __('webshop::mail.order.billing_title') }}</p>
                            <p style="margin: 0; font-size: 14px;">
                                {{ $mailBillingData['name'] ?? '' }}<br>
                                {{ $mailBillingData['zip'] ?? '' }} {{ $mailBillingData['city'] ?? '' }}<br>
                                {{ $mailBillingData['address'] ?? '' }}
                            </p>
                        </div>
                    @endif

                    @if($mailShippingData && $order->shipping_method !== 'pickup')
                        <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                            <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">{{ __('webshop::mail.order.shipping_title') }}</p>
                            <p style="margin: 0; font-size: 14px;">
                                {{ $mailShippingData['zip'] ?? '' }} {{ $mailShippingData['city'] ?? '' }}<br>
                                {{ $mailShippingData['address'] ?? '' }}
                            </p>
                        </div>
                    @endif
                </div>

                @if($order->note)
                    <div style="margin-top: 10px; padding: 10px; background-color: #fefefe; border: 1px dashed #ccc; font-size: 14px;">
                        <strong>{{ __('webshop::mail.order.note') }}</strong><br>
                        {{ $order->note }}
                    </div>
                @endif

                {{-- Jogi tájékoztatás (csak valódi rendelésnél). A szerződést tartós adathordozón kell
                     visszaigazolni az eladó adataival, a szerződési feltételekkel és az elállási
                     tájékoztatással, nyilatkozatmintával együtt (45/2014. Korm. rendelet).
                     FIGYELEM: ebben a fájlban fent egysoros PHP-direktíva van, ezért itt lent
                     blokk-formájú PHP-t NEM szabad használni – a Blade lenyelné a köztes részt. --}}
                @if($isOrder ?? false)
                    <div style="margin-top: 30px;">
                        <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                            {{ __('webshop::mail.legal.title') }}
                        </p>

                        @if(!empty($termsAttached) || !empty($termsUrl))
                            <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                                <strong>{{ __('webshop::mail.legal.terms_label') }}</strong>
                                {{ __('webshop::mail.legal.terms_text') }}
                                @if(!empty($termsAttached)) {{ __('webshop::mail.legal.terms_attached') }} @endif
                                @if(!empty($termsUrl)) {{ __('webshop::mail.legal.terms_online') }} <a href="{{ $termsUrl }}" style="color: #333;">{{ __('webshop::mail.legal.terms_link') }}</a>. @endif
                            </p>
                        @endif

                        <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                            <strong>{{ __('webshop::mail.legal.withdrawal_label') }}</strong>
                            {{ __('webshop::mail.legal.withdrawal_text') }}
                            @if(!empty($withdrawalUrl))
                                {{ __('webshop::mail.legal.withdrawal_online') }}
                                <a href="{{ $withdrawalUrl }}" style="color: #333; font-weight: bold;">{{ __('webshop::mail.legal.withdrawal_link') }}</a>.
                            @endif
                            {{ __('webshop::mail.legal.withdrawal_statement') }}
                        </p>

                        @if(!empty($privacyUrl))
                            <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                                <strong>{{ __('webshop::mail.legal.privacy_label') }}</strong>
                                {!! __('webshop::mail.legal.privacy_text', ['link' => '<a href="' . e($privacyUrl) . '" style="color: #333;">' . e(__('webshop::mail.legal.privacy_link')) . '</a>']) !!}
                            </p>
                        @endif

                        @if(!empty($seller['name']))
                            <p style="font-weight: bold; margin: 18px 0 5px; border-bottom: 1px solid #eee;">{{ __('webshop::mail.seller.title') }}</p>
                            <p style="margin: 0 0 15px; font-size: 13px; color: #333; line-height: 1.6;">
                                {{ $seller['name'] }}<br>
                                @if(!empty($seller['seat'])) {{ __('webshop::mail.seller.seat') }} {{ $seller['seat'] }}<br> @endif
                                @if(!empty($seller['registration_number'])) {{ __('webshop::mail.seller.registration_number') }} {{ $seller['registration_number'] }}<br> @endif
                                @if(!empty($seller['tax_number'])) {{ __('webshop::mail.seller.tax_number') }} {{ $seller['tax_number'] }}<br> @endif
                                @if(!empty($seller['email'])) {{ __('webshop::mail.seller.email') }} {{ $seller['email'] }}<br> @endif
                                @if(!empty($seller['phone'])) {{ __('webshop::mail.seller.phone') }} {{ $seller['phone'] }} @endif
                            </p>
                        @endif

                        {{-- Elállási nyilatkozatminta (45/2014. Korm. rendelet 2. melléklet), a rendelés adataival előtöltve --}}
                        <div style="border: 1px solid #ccc; padding: 12px 14px; font-size: 13px; color: #333; line-height: 1.6;">
                            <p style="font-weight: bold; margin: 0;">{{ __('webshop::mail.withdrawal_form.title') }}</p>
                            <p style="margin: 0 0 8px; font-style: italic;">{{ __('webshop::mail.withdrawal_form.instruction') }}</p>
                            <p style="margin: 0 0 6px;">
                                {{ __('webshop::mail.withdrawal_form.recipient', ['recipient' => implode(', ', array_filter([$seller['name'] ?? null, $seller['seat'] ?? null, $seller['email'] ?? null]))]) }}
                            </p>
                            <p style="margin: 0 0 6px;">
                                {{ __('webshop::mail.withdrawal_form.declaration') }}
                                @foreach($order->items as $item){{ $item->product_name }} ({{ trans_choice('webshop::common.quantity_pcs', $item->quantity, ['count' => $item->quantity]) }})@if(!$loop->last), @endif @endforeach
                                – {{ __('webshop::mail.withdrawal_form.order_number', ['number' => $order->order_number]) }}
                            </p>
                            <p style="margin: 0 0 6px;">
                                {{ __('webshop::mail.withdrawal_form.dates', ['ordered_at' => WsFormat::date($order->created_at)]) }}
                            </p>
                            <p style="margin: 0 0 6px;">{{ __('webshop::mail.withdrawal_form.consumer_name', ['name' => $order->customer_name]) }}</p>
                            <p style="margin: 0 0 6px;">
                                {{ __('webshop::mail.withdrawal_form.consumer_address', ['address' => implode(', ', array_filter([
                                    trim(($mailBillingData['zip'] ?? ($mailShippingData['zip'] ?? '')) . ' ' . ($mailBillingData['city'] ?? ($mailShippingData['city'] ?? ''))),
                                    $mailBillingData['address'] ?? ($mailShippingData['address'] ?? null),
                                ]))]) }}
                            </p>
                            <p style="margin: 0 0 6px;">{{ __('webshop::mail.withdrawal_form.signature') }}</p>
                            <p style="margin: 0;">{{ __('webshop::mail.withdrawal_form.date') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="100%">
            <div style="text-align: center; padding-bottom: 20px;">
                <p style="margin: 0 0 5px; font-weight: 600; font-size: 15px">{{ __('webshop::mail.regards') }}</p>
                <p style="margin: 0; line-height: 1.2; font-size: 14px; font-style: italic;">
                    {{ config('app.shop_name') }}
                </p>
            </div>
        </td>
    </tr>
@endsection
