@extends('admin.layouts.layout')
@section('title', 'Mérési scriptek')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-code"></i> Mérési scriptek</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Oldal típus</th>
                                    <th>Script eleje</th>
                                    <th style="width: 100px">Aktív</th>
                                    <th style="width: 100px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scripts as $script)
                                    <tr>
                                        <td>{{ $script->name }}</td>
                                        <td>{{ $pageTypes[$script->page_type] ?? $script->page_type }}</td>
                                        <td><code class="small text-muted">{{ \Illuminate\Support\Str::limit($script->script, 50) }}</code></td>
                                        <td class="text-center">
                                            @if($script->is_active)
                                                <span class="badge badge-success">Aktív</span>
                                            @else
                                                <span class="badge badge-secondary">Inaktív</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button type="button" class="btn btn-sm btn-info mr-2 ws-edit-script"
                                                        data-id="{{ $script->id }}"
                                                        data-name="{{ $script->name }}"
                                                        data-page="{{ $script->page_type }}"
                                                        data-script="{{ $script->script }}"
                                                        data-active="{{ $script->is_active ? 1 : 0 }}"
                                                        data-url="{{ route('admin.webshop.extra-settings.scripts.update', $script) }}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.webshop.extra-settings.scripts.destroy', $script) }}" method="POST" onsubmit="return confirm('Biztosan törli?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Nincsenek scriptek.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <h3 class="header-box product-info">Új script hozzáadása</h3>
                <div class="content-box bordered">
                    <form action="{{ route('admin.webshop.extra-settings.scripts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Script neve (pl: GA4, FB Pixel)</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="page_type">Oldal típus</label>
                                <select class="form-control" id="page_type" name="page_type" required>
                                    @foreach($pageTypes as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="script">Script kód (HTML/JS)</label>
                            <textarea class="form-control" id="script" name="script" rows="10" required></textarea>
                            <small class="text-muted">A kód változtatás nélkül kerül beillesztésre az adott oldal <code>@stack('scripts')</code> részébe.</small><br>
                            <small class="text-muted fw-600"><code>script</code> tag-eket használni kell!</small>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Aktív</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Hozzáadás</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="admin-save-box" style="left: 0">
            <div class="admin-save-btn-container mx-auto text-center">
                <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-secondary">Vissza</a>
            </div>
        </div>
    </div>

    <!-- Edit Script Modal -->
    <div class="modal fade" id="editScriptModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="editScriptForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Script szerkesztése</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="edit_name">Script neve (pl: GA4, FB Pixel)</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="edit_page_type">Oldal típus</label>
                                <select class="form-control" id="edit_page_type" name="page_type" required>
                                    @foreach($pageTypes as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_script">Script kód (HTML/JS)</label>
                            <textarea class="form-control" id="edit_script" name="script" rows="10" required></textarea>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_is_active">Aktív</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                        <button type="submit" class="btn btn-primary">Mentés</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.ws-edit-script').on('click', function() {
            var btn = $(this);
            var modal = $('#editScriptModal');
            var form = $('#editScriptForm');

            form.attr('action', btn.data('url'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_page_type').val(btn.data('page'));
            $('#edit_script').val(btn.data('script'));
            $('#edit_is_active').prop('checked', btn.data('active') == 1);

            modal.modal('show');
        });
    });
</script>
@endpush
