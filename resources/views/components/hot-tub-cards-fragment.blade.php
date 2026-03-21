@foreach($items as $it)
    @include('components.hot-tub-card', ['it' => $it])
@endforeach
