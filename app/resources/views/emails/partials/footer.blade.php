<div class="footer">
    <p>{{ __('This email was sent automatically by') }} <a href="{{ config('app.url') }}">{{ $organization->name ?? config('app.name') }}</a></p>
    @if($organization->email ?? false)
        <p>{{ __('Questions?') }} <a href="mailto:{{ $organization->email }}">{{ $organization->email }}</a></p>
    @endif
    <p>&copy; {{ date('Y') }} {{ $organization->name ?? config('app.name') }}. {{ __('All rights reserved.') }}</p>
</div>
