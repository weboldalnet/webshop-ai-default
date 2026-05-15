@php $isEdit = isset($property) && $property; @endphp
<div class="form-group">
    <label for="name">Név <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $isEdit ? $property->name : '') }}" required>
</div>
