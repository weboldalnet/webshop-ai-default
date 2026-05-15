@php $isEdit = isset($propertyCategory) && $propertyCategory; @endphp

<div class="form-group">
    <label for="name">Név <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $isEdit ? $propertyCategory->name : '') }}" required>
</div>

<div class="form-group">
    <label for="filter_type">Szűrő típus <span class="text-danger">*</span></label>
    <select class="form-control js-filter-type" id="filter_type" name="filter_type" required>
        <option value="checkbox" @if(old('filter_type', $isEdit ? $propertyCategory->filter_type : 'checkbox') === 'checkbox') selected @endif>Checkbox</option>
        <option value="radio" @if(old('filter_type', $isEdit ? $propertyCategory->filter_type : '') === 'radio') selected @endif>Radio</option>
        <option value="number" @if(old('filter_type', $isEdit ? $propertyCategory->filter_type : '') === 'number') selected @endif>Number</option>
    </select>
</div>

<div class="form-group js-suffix-group" style="{{ old('filter_type', $isEdit ? $propertyCategory->filter_type : 'checkbox') !== 'number' ? 'display:none' : '' }}">
    <label for="suffix">Suffix (mértékegység)</label>
    <input type="text" class="form-control" id="suffix" name="suffix" value="{{ old('suffix', $isEdit ? $propertyCategory->suffix : '') }}" placeholder="pl. kg, cm, db">
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="filter_enabled" name="filter_enabled" value="1"
               @if(old('filter_enabled', $isEdit ? $propertyCategory->filter_enabled : false)) checked @endif>
        <label class="custom-control-label" for="filter_enabled">Szűrőként megjelenik</label>
    </div>
</div>
