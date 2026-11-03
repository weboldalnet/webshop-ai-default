@extends('mail/layouts/mail_layout')

{{-- Lemondó levél a vásárlónak: a határidőn belül nem érkezett meg az online fizetés (webshop:cancel-unpaid-orders).
     Az elállási levéllel azonos szerkezet és stílus. --}}
@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        {{ __('webshop::mail.greeting', ['name' => $order->customer_name]) }}
                    </span>
                </div>

                <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                    {!! __('webshop::mail.cancelled.intro', [
                        'number' => '<strong>' . e($order->order_number) . '</strong>',
                        'within' => e(trans_choice('webshop::mail.cancelled.within_hours', $hours)),
                    ]) !!}
                </div>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9; width: 45%;">{{ __('webshop::common.order_number') }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">{{ __('webshop::mail.cancelled.placed_at') }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            {{ WsFormat::datetime(optional($order->created_at)->timezone(config('app.timezone'))) }}
                        </td>
                    </tr>
                </table>

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    {{ __('webshop::mail.cancelled.items_title') }}
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ __('webshop::common.product') }}</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">{{ __('webshop::mail.qty_short') }}</th>
                            @if($showPrices)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">{{ __('webshop::common.total') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->product_name }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ trans_choice('webshop::common.quantity_pcs', $item->quantity, ['count' => $item->quantity]) }}</td>
                                @if($showPrices)
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::money($item->total_price) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="font-size: 15px; color: #333; margin-bottom: 20px;">
                    {{ __('webshop::mail.cancelled.reorder_text') }}
                </div>
                <div style="text-align: center; margin-bottom: 20px;">
                    <a href="{{ $shopUrl }}" style="display: inline-block; padding: 10px 24px; background-color: {{ $buttonColor }}; color: #fff; text-decoration: none; border-radius: 4px; font-size: 15px; font-weight: bold;">{{ __('webshop::mail.cancelled.shop_button') }}</a>
                </div>

                @if(!empty($contactData['email']))
                    <p style="font-size: 14px; color: #555; margin-top: 20px;">
                        {{ __('webshop::mail.cancelled.contact', ['email' => $contactData['email']]) }}
                    </p>
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
