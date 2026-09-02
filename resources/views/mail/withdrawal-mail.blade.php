@extends('mail/layouts/mail_layout')

@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        @if($isShopCopy)
                            Új elállási kérelem érkezett
                        @else
                            Kedves {{ $withdrawal->customer_name }}!
                        @endif
                    </span>
                </div>

                <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                    @if($isShopCopy)
                        A(z) <strong>{{ $withdrawal->order_number }}</strong> számú rendeléshez elállási kérelem
                        érkezett {{ $withdrawal->customer_name }} ({{ $withdrawal->customer_email }}) részéről.
                    @else
                        Elállási kérelmét megkaptuk a(z) <strong>{{ $withdrawal->order_number }}</strong> számú
                        rendeléséhez. Kérelmét feldolgozzuk, és hamarosan felvesszük Önnel a kapcsolatot.
                    @endif
                </div>

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    Az elállással érintett termékek
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Termék</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">Mh.</th>
                            @if($showPrices)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">Egységár</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">Összesen</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawal->items as $item)
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->product_name }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $item->quantity }} db</td>
                                @if($showPrices)
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($item->unit_price, 0, ',', ' ') }} Ft</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($item->total_price, 0, ',', ' ') }} Ft</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if($showPrices)
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">
                                    Érintett érték összesen:
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">
                                    {{ number_format($withdrawal->total_amount, 0, ',', ' ') }} Ft
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                @if($withdrawal->is_full)
                    <p style="font-size: 14px; color: #333; margin-bottom: 20px;">
                        A kérelem a <strong>teljes rendelésre</strong> vonatkozik.
                    </p>
                @endif

                @if($withdrawal->reason)
                    <div style="margin-top: 10px; padding: 10px; background-color: #fefefe; border: 1px dashed #ccc; font-size: 14px;">
                        <strong>Az elállás indoklása:</strong><br>
                        {{ $withdrawal->reason }}
                    </div>
                @endif

                @if(!$isShopCopy && !empty($contactData['email']))
                    <p style="font-size: 14px; color: #555; margin-top: 20px;">
                        Kérdés esetén írjon nekünk a(z) {{ $contactData['email'] }} címre.
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
