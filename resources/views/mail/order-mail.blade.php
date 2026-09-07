@extends('mail/layouts/mail_layout')

@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        Kedves {{ $order->customer_name }}!
                    </span>
                </div>

                @if($customContent && $customContent->content)
                    <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                        {!! $customContent->content !!}
                    </div>
                @endif

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    Rendelés adatai (#{{ $order->order_number }})
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Termék</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">Mh.</th>
                            @if($showPrices && $order->total_price > 0)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">Egységár</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">Összesen</th>
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
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($item->unit_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($item->total_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if($showPrices && $order->total_price > 0)
                        {{-- Fizetendő bontása. A tételek összege önmagában nem adja ki a
                             végösszeget (szállítás, kupon), ezért mindkettőt kiírjuk –
                             enélkül a levont kedvezmény nyomtalanul eltűnne a levélből. --}}
                        @php $wsBontas = $order->priceBreakdown(); @endphp
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Termékek összesen:</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBontas['tetelek'], 0, '.', ' ') }} {{ $order->currency }}</td>
                            </tr>

                            @if($order->shipping_method)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Szállítási díj:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        {{ $wsBontas['szallitas'] > 0 ? number_format($wsBontas['szallitas'], 0, '.', ' ') . ' ' . $order->currency : 'Ingyenes' }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBontas['kedvezmeny'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        Kuponkedvezmény{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right; color: #1e7e34;">
                                        -{{ number_format($wsBontas['kedvezmeny'], 0, '.', ' ') }} {{ $order->currency }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBontas['utanvet'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Utánvét díja:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBontas['utanvet'], 0, '.', ' ') }} {{ $order->currency }}</td>
                                </tr>
                            @endif

                            @if($wsBontas['egyeb'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Egyéb díjak:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBontas['egyeb'], 0, '.', ' ') }} {{ $order->currency }}</td>
                                </tr>
                            @endif

                            <tr style="font-weight: bold; background-color: #f9f9f9;">
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Végösszeg:</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($order->total_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                <div style="display: flex; flex-wrap: wrap; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 250px; margin-right: 20px; margin-bottom: 15px;">
                        <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">Személyes adatok</p>
                        <p style="margin: 0; font-size: 14px;">
                            <strong>Név:</strong> {{ $order->customer_name }}<br>
                            <strong>Email:</strong> {{ $order->customer_email }}<br>
                            @if($order->customer_phone) <strong>Tel:</strong> {{ $order->customer_phone }}<br> @endif
                            @if($order->customer_company) <strong>Cég:</strong> {{ $order->customer_company }}<br> @endif
                            @if($order->customer_tax_number) <strong>Adószám:</strong> {{ $order->customer_tax_number }}<br> @endif
                        </p>
                    </div>

                    @php($mailBillingData = $order->getBillingDataArray())
                    @php($mailShippingData = $order->getShippingDataArray())

                    @if($mailBillingData)
                        <div style="flex: 1; min-width: 250px; margin-right: 20px; margin-bottom: 15px;">
                            <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">Számlázási adatok</p>
                            <p style="margin: 0; font-size: 14px;">
                                {{ $mailBillingData['name'] ?? '' }}<br>
                                {{ $mailBillingData['zip'] ?? '' }} {{ $mailBillingData['city'] ?? '' }}<br>
                                {{ $mailBillingData['address'] ?? '' }}
                            </p>
                        </div>
                    @endif

                    @if($mailShippingData && $order->shipping_method !== 'pickup')
                        <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
                            <p style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #eee;">Szállítási adatok</p>
                            <p style="margin: 0; font-size: 14px;">
                                {{ $mailShippingData['zip'] ?? '' }} {{ $mailShippingData['city'] ?? '' }}<br>
                                {{ $mailShippingData['address'] ?? '' }}
                            </p>
                        </div>
                    @endif
                </div>

                @if($order->note)
                    <div style="margin-top: 10px; padding: 10px; background-color: #fefefe; border: 1px dashed #ccc; font-size: 14px;">
                        <strong>Megjegyzés a rendeléshez:</strong><br>
                        {{ $order->note }}
                    </div>
                @endif
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="100%">
            <div style="text-align: center; padding-bottom: 20px;">
                <p style="margin: 0 0 5px; font-weight: 600; font-size: 15px">Üdvözlettel,</p>
                <p style="margin: 0; line-height: 1.2; font-size: 14px; font-style: italic;">
                    {{ config('app.shop_name') }}
                </p>
            </div>
        </td>
    </tr>
@endsection
