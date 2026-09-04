{{-- Kategóriablokk: ugyanaz a kártyasor, kategóriakártyával --}}
@include('site.webshop.home.partials.card-row', [
    'cardView' => 'site.webshop.categories.partials.category-card',
    'cardVar'  => 'cat',
])
