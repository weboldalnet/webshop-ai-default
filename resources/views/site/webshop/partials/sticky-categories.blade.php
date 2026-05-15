<div class="category-container sticky-top bg-white shadow-sm ws-category-nav">
    <div class="container-xl container-fluid">
        <div id="productCategoryList" class="category-box pt-2">
            <button type="button" class="btn category-mover" onclick="categoryMover($(this))">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </button>
            <div class="category-row d-flex flex-wrap">
                @foreach($stickyCategories as $cat)
                    <div class="dropdown mr-2 mb-2">
                        <a href="{{ route('site.webshop.categories.show', $cat) }}"
                           class="btn btn-outline-dark btn-category @if(isset($category) && ($category->id == $cat->id || $category->parent_id == $cat->id)) active @endif @if($cat->children->isNotEmpty()) dropdown-toggle @endif"
                           @if($cat->children->isNotEmpty()) data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @endif
                        >
                            {{ $cat->name_singular }}
                        </a>
                        @if($cat->children->isNotEmpty())
                            <div class="dropdown-menu">
                                @foreach($cat->children as $child)
                                    <a class="dropdown-item" href="{{ route('site.webshop.categories.show', $child) }}">{{ $child->name_singular }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
