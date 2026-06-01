<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}">

<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="loginpage">

    <div class="tt-auth-page">

        <div class="tt-auth-card">

            {{-- Brand / Logo --}}
            <div class="tt-auth-brand">
                @if($logoPath != '' && !str_ends_with($logoPath, "dist/images/logo.svg"))
                    <a href="{!! BASE_URL !!}">
                        <img src="{{ $logoPath }}" class="tt-auth-logo" alt="Toba Tech Portal" />
                    </a>
                @else
                    <a href="{!! BASE_URL !!}" class="tt-auth-wordmark">Toba Tech Portal</a>
                @endif
            </div>

            {{-- Form content --}}
            <div class="regpanelinner">
                @if(isset($action) && isset($module))
                @include("$module::$action")
                @else
                @yield('content')
                @endif
            </div>

        </div>

        <div class="tt-auth-footer">
            <span>© Toba Tech Portal</span>
        </div>

    </div>

    @include('global::sections.pageBottom')
    @stack('scripts')
</body>

</html>
