@foreach($items as $it)
    @include('components.outdoor-product-card', ['it' => $it])
@endforeach
