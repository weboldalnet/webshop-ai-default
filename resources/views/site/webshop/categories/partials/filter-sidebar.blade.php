<div class="ws-filter-box card mb-4">
    <div class="card-header bg-dark text-white font-weight-bold">
        <i class="fa fa-filter"></i> Szűrők
    </div>
    <div class="card-body">
        <form id="ws-filter-form">
            @foreach($category->propertyCategories()->where('filter_enabled', true)->orderBy('sort_order')->get() as $pc)
                <div class="ws-filter-group mb-4">
                    <h6 class="font-weight-bold border-bottom pb-2">{{ $pc->name }} @if($pc->suffix)<small class="text-muted">({{ $pc->suffix }})</small>@endif</h6>
                    
                    @if($pc->filter_type === 'number')
                        <div class="row no-gutters">
                            <div class="col-6 pr-1">
                                <input type="number" name="n[{{ $pc->id }}][min]" class="form-control form-control-sm js-filter-input" placeholder="Min" value="{{ request("n.{$pc->id}.min") }}">
                            </div>
                            <div class="col-6 pl-1">
                                <input type="number" name="n[{{ $pc->id }}][max]" class="form-control form-control-sm js-filter-input" placeholder="Max" value="{{ request("n.{$pc->id}.max") }}">
                            </div>
                        </div>
                    @else
                        @foreach($pc->properties()->active()->ordered()->get() as $prop)
                            <div class="custom-control custom-{{ $pc->filter_type === 'radio' ? 'radio' : 'checkbox' }} mb-1">
                                <input type="{{ $pc->filter_type === 'radio' ? 'radio' : 'checkbox' }}" 
                                       class="custom-control-input js-filter-input" 
                                       id="f{{ $prop->id }}" 
                                       name="f[{{ $pc->id }}]{{ $pc->filter_type === 'radio' ? '' : '[]' }}" 
                                       value="{{ $prop->id }}"
                                       @if(is_array(request("f.{$pc->id}")) ? in_array($prop->id, request("f.{$pc->id}")) : request("f.{$pc->id}") == $prop->id) checked @endif
                                >
                                <label class="custom-control-label" for="f{{ $prop->id }}">{{ $prop->name }}</label>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach

            <button type="button" class="btn btn-outline-danger btn-sm btn-block mt-3 js-filter-clear">
                <i class="fa fa-times"></i> Szűrők törlése
            </button>
        </form>
    </div>
</div>
