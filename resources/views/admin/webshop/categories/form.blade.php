@php
    $isEdit = isset($category) && $category;
    $selectedPropCats = $isEdit ? $category->propertyCategories->pluck('id')->toArray() : [];
    $showOnCardIds = $isEdit ? $category->propertyCategories->where('pivot.show_on_product_card', true)->pluck('id')->toArray() : [];
    $selectedRelated = $isEdit ? $category->relatedCategories->pluck('id')->toArray() : [];
@endphp

<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Alapadatok</h3>
        <div class="content-box bordered">
            <div class="form-group">
                <label for="name_singular">Név (egyes szám) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_singular" name="name_singular" value="{{ old('name_singular', $isEdit ? $category->name_singular : '') }}" required>
            </div>
            <div class="form-group">
                <label for="name_plural">Név (többes szám) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_plural" name="name_plural" value="{{ old('name_plural', $isEdit ? $category->name_plural : '') }}" required>
            </div>
            <div class="form-group">
                <label for="description">Leírás</label>
                <textarea class="form-control js-tinymce" id="description" name="description" rows="4">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info"><i class="fa-solid fa-code"></i> SEO / OG adatok</h3>
        <div class="content-box bordered">
            @if($isEdit && $category->og_img)
                <div class="mb-2"><img src="{{ $category->og_img }}" class="img-fluid" style="max-height:150px"></div>
            @endif
            <div class="form-group">
                <label for="og_img">Megosztási kép (1200x630)</label>
                <input type="file" class="form-control-file" id="og_img" name="og_img" accept=".jpg,.jpeg,.png">
            </div>
            <div class="form-group">
                <label for="og_title">OG Cím</label>
                <input type="text" class="form-control" id="og_title" name="og_title" maxlength="60" value="{{ old('og_title', $isEdit ? $category->og_title : '') }}" placeholder="Alapértelmezett: kategória neve">
            </div>
            <div class="form-group">
                <label for="og_description">OG Leírás</label>
                <textarea class="form-control" id="og_description" name="og_description" maxlength="120">{{ old('og_description', $isEdit ? $category->og_description : '') }}</textarea>
            </div>
        </div>
    </div>
</div>

@if(($ws['category_parent_enabled'] ?? 'false') === 'true')
<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Szülő kategória</h3>
        <div class="content-box bordered">
            <div class="form-group">
                <label for="parent_id">Szülő kategória</label>
                <select class="form-control" id="parent_id" name="parent_id">
                    <option value="">-- Nincs --</option>
                    @foreach($allCategories as $cat)
                        <option value="{{ $cat->id }}" @if(old('parent_id', $isEdit ? $category->parent_id : '') == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
@endif

@if(($ws['category_icon_enabled'] ?? 'false') === 'true')
<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Ikon</h3>
        <div class="content-box bordered">
            @if($isEdit && $category->icon)
                <div class="mb-2"><img src="{{ $category->icon }}" style="max-height:60px"></div>
            @endif
            <div class="form-group">
                <label for="icon_file">Ikon (SVG/PNG)</label>
                <input type="file" class="form-control-file" id="icon_file" name="icon_file" accept=".svg,.png">
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Tulajdonság kategóriák</h3>
        <div class="content-box bordered">
            @foreach($propertyCategories as $pc)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="pc{{ $pc->id }}" name="property_categories[]" value="{{ $pc->id }}"
                           @if(in_array($pc->id, old('property_categories', $selectedPropCats))) checked @endif>
                    <label class="custom-control-label" for="pc{{ $pc->id }}">{{ $pc->name }} <span class="badge badge-info">{{ $pc->filter_type }}</span></label>
                </div>
                @if(($ws['category_product_card_properties_enabled'] ?? 'false') === 'true')
                    <div class="ml-4 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="card{{ $pc->id }}" name="show_on_product_card[]" value="{{ $pc->id }}"
                                   @if(in_array($pc->id, old('show_on_product_card', $showOnCardIds))) checked @endif>
                            <label class="custom-control-label text-muted" for="card{{ $pc->id }}"><small>Megjelenik termékkártyán</small></label>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if(($ws['category_related_enabled'] ?? 'false') === 'true')
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Kapcsolódó kategóriák</h3>
        <div class="content-box bordered">
            @foreach($allCategories as $cat)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="rel{{ $cat->id }}" name="related_categories[]" value="{{ $cat->id }}"
                           @if(in_array($cat->id, old('related_categories', $selectedRelated))) checked @endif>
                    <label class="custom-control-label" for="rel{{ $cat->id }}">{{ $cat->name_singular }}</label>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
