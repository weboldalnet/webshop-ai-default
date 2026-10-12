@extends('admin.layouts.layout')
@section('title', 'Kötelező termékinformációk')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box my-2"><i class="fa fa-clipboard-list"></i> Kötelező termékinformációk</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        <form action="{{ route('admin.webshop.extra-settings.product-info.store') }}" method="POST">
            @csrf

            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Mit árul a bolt?</h3>
                    <div class="content-box bordered mb-3">
                        <p class="text-muted fs-14">
                            Online értékesítésnél a termék fajtájától függően bizonyos adatoknak a vásárlás előtt, a
                            termékoldalon kell látszaniuk. Kapcsold be a boltra vonatkozó termékköröket: a termékszerkesztőben
                            ezek mezői jelennek meg, a termékoldalon pedig egy „Termékinformációk” fül mutatja a kitöltött adatokat.
                        </p>
                        @foreach($profiles as $wsProfile => $wsDefinition)
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="profile_{{ $wsProfile }}" name="profiles[]"
                                       value="{{ $wsProfile }}" @if(in_array($wsProfile, $enabledProfiles, true)) checked @endif>
                                <label class="custom-control-label" for="profile_{{ $wsProfile }}">
                                    <strong>{{ $wsDefinition['label'] }}</strong>
                                </label>
                                <span class="text-muted fs-14 d-block">{{ $wsDefinition['description'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Bolt szintű alapértékek</h3>
                    <div class="content-box bordered mb-3">
                        <p class="text-muted fs-14">
                            Ha a termékek többségénél ugyanaz az adat (pl. a bolt maga a gyártó vagy az élelmiszer-vállalkozó),
                            itt elég egyszer megadni. A termékszerkesztőben kitöltött érték felülírja.
                        </p>
                        <div class="row">
                            @foreach($profiles as $wsProfile => $wsDefinition)
                                @foreach($wsDefinition['fields'] as $wsField => $wsConfig)
                                    @if($wsConfig['default'] ?? false)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="default_{{ $wsProfile }}_{{ $wsField }}">
                                                    {{ $wsConfig['label'] }}
                                                    <span class="text-muted font-weight-normal">({{ $wsDefinition['label'] }})</span>
                                                </label>
                                                <textarea class="form-control" rows="3" id="default_{{ $wsProfile }}_{{ $wsField }}"
                                                          name="defaults[{{ $wsProfile }}][{{ $wsField }}]">{{ $defaults[$wsProfile][$wsField] ?? '' }}</textarea>
                                                @if(!empty($wsConfig['hint']))
                                                    <span class="text-muted fs-14">{{ $wsConfig['hint'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                        @if(!empty($seller['name']))
                            <span class="text-muted fs-14 d-block">
                                Ha a bolt saját terméket árul, az eladó adatai:
                                {{ implode(', ', array_filter([$seller['name'] ?? null, $seller['seat'] ?? null, $seller['email'] ?? null])) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="admin-save-box" style="left: 0">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-secondary">Vissza</a>
                </div>
            </div>
        </form>
    </div>
@endsection
