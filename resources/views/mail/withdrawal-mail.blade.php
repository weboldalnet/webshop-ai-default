@extends('mail/layouts/mail_layout')

@section('content')
    <tr>
        <td colspan="2">
            <div style="padding: 15px 20px 35px 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="font-size: 20px; color: #000; font-weight: bold; display: inline-block">
                        @if($isShopCopy)
                            {{ __('webshop::mail.withdrawal.shop_title') }}
                        @else
                            {{ __('webshop::mail.greeting', ['name' => $withdrawal->customer_name]) }}
                        @endif
                    </span>
                </div>

                <div style="font-size: 15px; color: #333; margin-bottom: 25px;">
                    @if($isShopCopy)
                        {!! __('webshop::mail.withdrawal.shop_intro', [
                            'number' => '<strong>' . e($withdrawal->order_number) . '</strong>',
                            'name' => e($withdrawal->customer_name),
                            'email' => e($withdrawal->customer_email),
                        ]) !!}
                    @else
                        {!! __('webshop::mail.withdrawal.customer_intro', ['number' => '<strong>' . e($withdrawal->order_number) . '</strong>']) !!}
                    @endif
                </div>

                {{-- A visszaigazolásnak tartós adathordozón tartalmaznia kell a nyilatkozat tartalmát,
                     valamint beérkezésének dátumát és időpontját (uniós „elállási funkció”, 2026. június 19-től). --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9; width: 45%;">{{ __('webshop::mail.withdrawal.received_at') }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">
                            {{ WsFormat::datetime(optional($withdrawal->created_at)->timezone(config('app.timezone'))) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">{{ __('webshop::common.order_number') }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $withdrawal->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">{{ __('webshop::mail.withdrawal.submitted_by') }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $withdrawal->customer_name }} ({{ $withdrawal->customer_email }})</td>
                    </tr>
                </table>

                <p style="font-size: 17px; color: #000; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    {{ __('webshop::mail.withdrawal.items_title') }}
                </p>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ __('webshop::common.product') }}</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; width: 60px;">{{ __('webshop::mail.qty_short') }}</th>
                            @if($showPrices)
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">{{ __('webshop::common.unit_price_label') }}</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; width: 100px;">{{ __('webshop::common.total') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawal->items as $item)
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->product_name }}</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ trans_choice('webshop::common.quantity_pcs', $item->quantity, ['count' => $item->quantity]) }}</td>
                                @if($showPrices)
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::money($item->unit_price) }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ WsFormat::money($item->total_price) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if($showPrices)
                        <tfoot>
                            <tr>
                                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">
                                    {{ __('webshop::mail.withdrawal.total') }}
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">
                                    {{ WsFormat::money($withdrawal->total_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                @if($withdrawal->is_full)
                    <p style="font-size: 14px; color: #333; margin-bottom: 20px;">
                        {!! __('webshop::mail.withdrawal.is_full', ['scope' => '<strong>' . e(__('webshop::mail.withdrawal.full_order')) . '</strong>']) !!}
                    </p>
                @endif

                @if(filled($withdrawal->reason))
                    <div style="margin-top: 10px; padding: 10px; background-color: #fefefe; border: 1px dashed #ccc; font-size: 14px;">
                        <strong>{{ __('webshop::mail.withdrawal.note') }}</strong><br>
                        {{ $withdrawal->reason }}
                    </div>
                @endif

                @if(!$isShopCopy && !empty($contactData['email']))
                    <p style="font-size: 14px; color: #555; margin-top: 20px;">
                        {{ __('webshop::mail.withdrawal.contact', ['email' => $contactData['email']]) }}
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
