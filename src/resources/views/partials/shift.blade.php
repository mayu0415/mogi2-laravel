@if (Auth::check())
    @include('header-auth')
@else
    @include('header-guest')
@endif