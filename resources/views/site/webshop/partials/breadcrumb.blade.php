<nav aria-label="breadcrumb" class="ws-breadcrumb mt-3">
    <ol class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item"><a href="{{ route('site.webshop.categories.index') }}"><i class="fa fa-home text-dark"></i></a></li>
        
        @if(isset($category))
            @if($category->parent)
                <li class="breadcrumb-item"><a href="{{ route('site.webshop.categories.show', $category->parent) }}" class="text-dark">{{ $category->parent->name_singular }}</a></li>
            @endif
            <li class="breadcrumb-item @if(!isset($product)) active @endif" @if(!isset($product)) aria-current="page" @endif>
                @if(isset($product))
                    <a href="{{ route('site.webshop.categories.show', $category) }}" class="text-dark">{{ $category->name_singular }}</a>
                @else
                    {{ $category->name_singular }}
                @endif
            </li>
        @endif

        @if(isset($product))
            <li class="breadcrumb-item active" aria-current="page text-dark">{{ $product->name }}</li>
        @endif
    </ol>
</nav>
