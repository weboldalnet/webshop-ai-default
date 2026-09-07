@extends('admin.layouts.layout')
@section('title', 'Termék címkék')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row align-items-center mb-3">
            <div class="col-12">
                <h2 class="header-box mb-0"><i class="fa fa-tags"></i> Termék címkék</h2>
            </div>
            <div class="col-12 text-center mt-3">
                <a href="{{ route('admin.webshop.labels.create') }}" class="btn btn-primary fw-600 fs-18">
                    <i class="fa fa-plus-circle"></i> Új címke létrehozása
                </a>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Név</th>
                            <th>Minta</th>
                            <th>Háttérszín</th>
                            <th>Szövegszín</th>
                            <th class="text-right">Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($labels as $label)
                            <tr>
                                <td>{{ $label->name }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $label->bg_color }}; color: {{ $label->text_color }}; padding: 5px 10px;">
                                        {{ $label->name }}
                                    </span>
                                </td>
                                <td><code>{{ $label->bg_color }}</code></td>
                                <td><code>{{ $label->text_color }}</code></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.webshop.labels.edit', $label) }}" class="btn btn-sm btn-info" title="Szerkesztés">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    {{-- A js-delete-btn + data-url a megosztott megerősítő modalt nyitja
                                         (WebshopAdmin.initDeleteConfirm). Korábban itt egy sima form volt
                                         "js-delete-form" osztállyal, amihez SEHOL nem tartozott JS – a
                                         beillesztett modal sosem jelent meg, a törlés egy kattintásra,
                                         megerősítés nélkül lefutott. --}}
                                    <button type="button" class="btn btn-sm btn-danger js-delete-btn" title="Törlés"
                                            data-url="{{ route('admin.webshop.labels.destroy', $label) }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nincsenek létrehozott címkék.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')

    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection
