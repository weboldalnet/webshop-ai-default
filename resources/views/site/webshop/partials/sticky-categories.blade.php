<div class="category-container sticky-top bg-white shadow-sm ws-category-nav">
    <div class="container-xl container-fluid">
        <div id="productCategoryList" class="category-box pt-2">
            <button type="button" class="btn category-mover" onclick="categoryMover($(this))">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </button>
            <div class="category-row d-flex flex-nowrap">
                @isset($visibleFilterBtn)
                    <button type="button" class="btn-category filter-btn d-lg-none d-block js-show-filter-btn">
                        <i class="fa fa-filter"></i>
                    </button>
                @endisset
                @foreach($stickyCategories as $cat)
                    <div class="dropdown mr-1 mb-0 js-category-dropdown" tabindex="0">
                        <a href="{{ route('site.webshop.categories.show', $cat) }}"
                           class="btn-category @if(isset($category) && ($category->id == $cat->id || $category->parent_id == $cat->id)) active @endif @if($cat->children->isNotEmpty()) dropdown-toggle @endif"
                           @if($cat->children->isNotEmpty()) data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @endif
                        >
                            {{ $cat->name_singular }}
                        </a>
                        @if($cat->children->isNotEmpty())
                            <div class="dropdown-menu shadow-sm py-1">
                                @foreach($cat->children as $child)
                                    <a class="dropdown-item fs-16 px-lg-3 px-2" href="{{ route('site.webshop.categories.show', $child) }}">{{ $child->name_singular }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
