@extends('mail/layouts/mail_layout')

{{-- Lemondó levél a vásárlónak: a határidőn belül nem érkezett meg az online fizetés (webshop:cancel-unpaid-orders).
     Az elállási levéllel azonos szerkezet és stílus. --}}
@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        Kedves {{ $order->customer_name }}!
                    </span>
                </div>

                <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                    A(z) <strong>{{ $order->order_number }}</strong> számú rendelését lemondtuk, mert a leadása után
                    {{ $hours }} órán belül nem érkezett meg az online fizetés. A rendelés miatt nem terheltünk meg semmit,
                    a lefoglalt termékeket pedig felszabadítottuk.
                </div>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9; width: 45%;">Rendelésszám</td>
                        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">A rendelés leadása</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            {{ optional($order->created_at)->timezone(config('app.timezone'))->format('Y. m. d. H:i') }}
                        </td>
                    </tr>
                </table>

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    A lemondott rendelés tételei
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Termék</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">Menny.</th>
                            @if($showPrices)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">Összesen</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->product_name }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $item->quantity }} db</td>
                                @if($showPrices)
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($item->total_price, 0, ',', ' ') }} Ft</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="font-size: 15px; color: #333; margin-bottom: 20px;">
                    Ha továbbra is szeretné megvásárolni a termékeket, adja le újra a rendelést a webshopban:
                </div>
                <div style="text-align: center; margin-bottom: 20px;">
                    <a href="{{ $shopUrl }}" style="display: inline-block; padding: 10px 24px; background-color: {{ $buttonColor }}; color: #fff; text-decoration: none; border-radius: 4px; font-size: 15px; font-weight: bold;">Tovább a webshopba</a>
                </div>

                @if(!empty($contactData['email']))
                    <p style="font-size: 14px; color: #555; margin-top: 20px;">
                        Ha a fizetést mégis elindította, vagy kérdése van, írjon nekünk a(z) {{ $contactData['email'] }} címre.
                    </p>
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
