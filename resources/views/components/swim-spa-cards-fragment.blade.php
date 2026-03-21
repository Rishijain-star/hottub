@foreach($items as $it)
    @include('components.swim-spa-card', ['it' => $it])
@endforeach
