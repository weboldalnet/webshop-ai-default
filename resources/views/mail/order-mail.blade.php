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
                        @php $wsBreakdown = $order->priceBreakdown(); @endphp
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Termékek összesen:</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBreakdown['items'], 0, '.', ' ') }} {{ $order->currency }}</td>
                            </tr>

                            @if($order->shipping_method)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Szállítási díj:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        {{ $wsBreakdown['shipping'] > 0 ? number_format($wsBreakdown['shipping'], 0, '.', ' ') . ' ' . $order->currency : 'Ingyenes' }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBreakdown['discount'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                                        Kuponkedvezmény{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}:
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right; color: #1e7e34;">
                                        -{{ number_format($wsBreakdown['discount'], 0, '.', ' ') }} {{ $order->currency }}
                                    </td>
                                </tr>
                            @endif

                            @if($wsBreakdown['cod'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Utánvét díja:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBreakdown['cod'], 0, '.', ' ') }} {{ $order->currency }}</td>
                                </tr>
                            @endif

                            @if($wsBreakdown['other'] > 0)
                                <tr>
                                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Egyéb díjak:</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($wsBreakdown['other'], 0, '.', ' ') }} {{ $order->currency }}</td>
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

                {{-- Jogi tájékoztatás (csak valódi rendelésnél). A szerződést tartós adathordozón kell
                     visszaigazolni az eladó adataival, a szerződési feltételekkel és az elállási
                     tájékoztatással, nyilatkozatmintával együtt (45/2014. Korm. rendelet).
                     FIGYELEM: ebben a fájlban fent egysoros PHP-direktíva van, ezért itt lent
                     blokk-formájú PHP-t NEM szabad használni – a Blade lenyelné a köztes részt. --}}
                @if($isOrder ?? false)
                    <div style="margin-top: 30px;">
                        <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                            Fontos tudnivalók
                        </p>

                        @if(!empty($termsAttached) || !empty($termsUrl))
                            <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                                <strong>Szerződési feltételek:</strong>
                                a rendelésre webáruházunk Általános Szerződési Feltételei vonatkoznak.
                                @if(!empty($termsAttached)) A feltételeket PDF-ben csatoltuk ehhez a levélhez. @endif
                                @if(!empty($termsUrl)) Online is megtekintheti: <a href="{{ $termsUrl }}" style="color: #333;">Általános Szerződési Feltételek</a>. @endif
                            </p>
                        @endif

                        <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                            <strong>Elállási jog:</strong>
                            a termék átvételétől számított 14 napon belül indoklás nélkül elállhat a vásárlástól.
                            @if(!empty($withdrawalUrl))
                                Ezt a legegyszerűbben itt teheti meg:
                                <a href="{{ $withdrawalUrl }}" style="color: #333; font-weight: bold;">Elállás a vásárlástól</a>.
                            @endif
                            Elállási szándékát bármilyen egyértelmű nyilatkozattal, vagy az alábbi nyilatkozatminta
                            kitöltésével és elküldésével is jelezheti.
                        </p>

                        @if(!empty($privacyUrl))
                            <p style="font-size: 14px; color: #333; margin: 0 0 10px;">
                                <strong>Adatkezelés:</strong>
                                személyes adatai kezeléséről az
                                <a href="{{ $privacyUrl }}" style="color: #333;">Adatvédelmi tájékoztatóban</a> olvashat.
                            </p>
                        @endif

                        @if(!empty($seller['name']))
                            <p style="font-weight: bold; margin: 18px 0 5px; border-bottom: 1px solid #eee;">Az eladó adatai</p>
                            <p style="margin: 0 0 15px; font-size: 13px; color: #333; line-height: 1.6;">
                                {{ $seller['name'] }}<br>
                                @if(!empty($seller['seat'])) Székhely: {{ $seller['seat'] }}<br> @endif
                                @if(!empty($seller['registration_number'])) Cégjegyzékszám / nyilvántartási szám: {{ $seller['registration_number'] }}<br> @endif
                                @if(!empty($seller['tax_number'])) Adószám: {{ $seller['tax_number'] }}<br> @endif
                                @if(!empty($seller['email'])) E-mail: {{ $seller['email'] }}<br> @endif
                                @if(!empty($seller['phone'])) Telefon: {{ $seller['phone'] }} @endif
                            </p>
                        @endif

                        {{-- Elállási nyilatkozatminta (45/2014. Korm. rendelet 2. melléklet), a rendelés adataival előtöltve --}}
                        <div style="border: 1px solid #ccc; padding: 12px 14px; font-size: 13px; color: #333; line-height: 1.6;">
                            <p style="font-weight: bold; margin: 0;">Elállási nyilatkozatminta</p>
                            <p style="margin: 0 0 8px; font-style: italic;">(csak a szerződéstől való elállási szándék esetén töltse ki és juttassa vissza)</p>
                            <p style="margin: 0 0 6px;">
                                Címzett: {{ implode(', ', array_filter([$seller['name'] ?? null, $seller['seat'] ?? null, $seller['email'] ?? null])) }}
                            </p>
                            <p style="margin: 0 0 6px;">
                                Alulírott kijelentem, hogy gyakorlom elállási jogomat az alábbi termék(ek) adásvételére irányuló
                                szerződés tekintetében:
                                @foreach($order->items as $item){{ $item->product_name }} ({{ $item->quantity }} db)@if(!$loop->last), @endif @endforeach
                                – rendelésszám: {{ $order->order_number }}
                            </p>
                            <p style="margin: 0 0 6px;">
                                Szerződéskötés időpontja / átvétel időpontja:
                                {{ optional($order->created_at)->format('Y. m. d.') }} / ………………………
                            </p>
                            <p style="margin: 0 0 6px;">A fogyasztó neve: {{ $order->customer_name }}</p>
                            <p style="margin: 0 0 6px;">
                                A fogyasztó címe:
                                {{ implode(', ', array_filter([
                                    trim(($mailBillingData['zip'] ?? ($mailShippingData['zip'] ?? '')) . ' ' . ($mailBillingData['city'] ?? ($mailShippingData['city'] ?? ''))),
                                    $mailBillingData['address'] ?? ($mailShippingData['address'] ?? null),
                                ])) }}
                            </p>
                            <p style="margin: 0 0 6px;">A fogyasztó aláírása (kizárólag papíron tett nyilatkozat esetén): ………………………</p>
                            <p style="margin: 0;">Kelt: ………………………</p>
                        </div>
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
