@extends('admin.layouts.layout')
@section('title', 'Új rendelés létrehozása')

@section('content')
    <div class="container-fluid mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-plus-circle"></i> Új rendelés létrehozása</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.webshop.orders.store') }}">
            @csrf

            <div class="row">
                <!-- Bal oszlop -->
                <div class="col-lg-6">
                    <!-- Rendelés alapadatok -->
                    <h3 class="header-box product-info mb-3">Rendelés alapadatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Típus</label>
                                <select name="type" class="form-control">
                                    @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::TYPES as $key => $label)
                                        <option value="{{ $key }}" @if(old('type') === $key) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Státusz</label>
                                <select name="status" class="form-control">
                                    @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUSES as $key => $label)
                                        <option value="{{ $key }}" @if(old('status', \Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUS_PENDING) === $key) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_completed" class="custom-control-input" id="isCompleted" value="1" @if(old('is_completed')) checked @endif>
                                    <label class="custom-control-label" for="isCompleted">Teljesített</label>
                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Admin megjegyzés</label>
                                <textarea name="admin_note" class="form-control" rows="2">{{ old('admin_note') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Vevő adatok -->
                    <h3 class="header-box product-info mb-3">Vevő adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Vevő neve <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Telefonszám</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Cégnév</label>
                                <input type="text" name="customer_company" class="form-control" value="{{ old('customer_company') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Adószám</label>
                                <input type="text" name="customer_tax_number" class="form-control" value="{{ old('customer_tax_number') }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Vevő megjegyzése</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobb oszlop -->
                <div class="col-lg-6">
                    <!-- Számlázási adatok -->
                    <h3 class="header-box product-info mb-3">Számlázási adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Számlázási név</label>
                                <input type="text" name="billing[name]" class="form-control" value="{{ old('billing.name') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Irsz.</label>
                                <input type="text" name="billing[zip]" class="form-control" value="{{ old('billing.zip') }}">
                            </div>
                            <div class="col-md-9 form-group">
                                <label>Város</label>
                                <input type="text" name="billing[city]" class="form-control" value="{{ old('billing.city') }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Utca, házszám</label>
                                <input type="text" name="billing[address]" class="form-control" value="{{ old('billing.address') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Szállítási adatok -->
                    <h3 class="header-box product-info mb-3">Szállítási adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Irsz.</label>
                                <input type="text" name="shipping[zip]" class="form-control" value="{{ old('shipping.zip') }}">
                            </div>
                            <div class="col-md-9 form-group">
                                <label>Város</label>
                                <input type="text" name="shipping[city]" class="form-control" value="{{ old('shipping.city') }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Utca, házszám</label>
                                <input type="text" name="shipping[address]" class="form-control" value="{{ old('shipping.address') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rendelési tételek -->
            <h3 class="header-box product-info mb-3">Rendelési tételek</h3>
            <div class="content-box bordered mb-4">
                
                <div class="row mb-4 bg-light p-3 rounded mx-0">
                    <div class="col-md-6">
                        <label>Termék keresése</label>
                        <input type="text" class="form-control js-admin-order-product-search" list="productSearchList" placeholder="Kezdje el gépelni a termék nevét...">
                        <datalist id="productSearchList">
                            @foreach($products as $product)
                                <option value="{{ $product->name }}" 
                                        data-id="{{ $product->id }}" 
                                        data-price="{{ $product->sale_price ?? $product->price ?? 0 }}">
                                    @if($product->sku) [{{ $product->sku }}] @endif {{ hufFormat($product->sale_price ?? $product->price ?? 0) }}
                                </option>
                            @endforeach
                        </datalist>
                        <input type="hidden" class="js-admin-order-product-id">
                        <input type="hidden" class="js-admin-order-product-price">
                    </div>
                    <div class="col-md-2">
                        <label>Darabszám</label>
                        <input type="number" class="form-control js-admin-order-product-qty" value="1" min="1">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-block js-admin-order-add-item">
                            <i class="fa fa-plus-circle mr-1"></i> Tétel hozzáadása
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Termék</th>
                                <th style="width: 120px;">Darabszám</th>
                                @if($pricesVisible)
                                    <th style="width: 180px;">Egységár</th>
                                    <th style="width: 180px;">Összesen</th>
                                @endif
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody class="js-admin-order-items">
                            {{-- JS tölti fel --}}
                            @if(old('items'))
                                @foreach(old('items') as $id => $item)
                                    <tr class="js-admin-order-item-row" data-id="{{ $id }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $id }}][product_id]" value="{{ $id }}">
                                            <input type="hidden" name="items[{{ $id }}][name]" value="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $id }}][quantity]" class="form-control form-control-sm js-admin-order-item-qty" value="{{ $item['quantity'] }}" min="1">
                                        </td>
                                        @if($pricesVisible)
                                            <td>
                                                @php
                                                    $product = \Weboldalnet\WebshopAiDefault\Models\WebshopProduct::find($id);
                                                    $price = $product ? ($product->sale_price ?? $product->price ?? 0) : 0;
                                                @endphp
                                                <input type="hidden" class="js-admin-order-item-price" value="{{ $price }}">
                                                {{ hufFormat($price) }}
                                            </td>
                                            <td>
                                                <span class="js-admin-order-line-total font-weight-bold">{{ hufFormat($price * $item['quantity']) }}</span>
                                            </td>
                                        @endif
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-danger js-admin-order-item-remove"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        @if($pricesVisible)
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right h5 font-weight-bold">Mindösszesen:</th>
                                    <th class="h5 font-weight-bold text-primary js-admin-order-grand-total">0 Ft</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-5">
                <a href="{{ route('admin.webshop.orders.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left mr-1"></i> Vissza a listához
                </a>
                <button type="submit" class="btn btn-success px-5 font-weight-bold shadow">
                    <i class="fa fa-save mr-1"></i> Rendelés létrehozása
                </button>
            </div>
        </form>
    </div>

    <style>
        @if(!$pricesVisible)
            .js-price-col { display: none; }
        @endif
    </style>

    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        $(function() {
            WebshopAdmin.initAdminOrderCreate();
        });
    </script>
@endsection
