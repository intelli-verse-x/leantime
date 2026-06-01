<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}" data-theme="{{ app(\Leantime\Core\UI\Theme::class)->getColorMode() }}">

<head>
    @include('global::sections.header')
    @stack('styles')
</head>

<body class="" hx-ext="preload">

    @include('global::sections.appAnnouncement')

    @php
    $ltMenuState = session('menuState') === 'closed' ? 'closed' : 'open';
    @endphp
    <div class="mainwrapper app menu{{ $ltMenuState }}">

        <header class="hdr">
            <button class="hdr-icon barmenu" aria-label="Toggle menu">
                <i class="fa-solid fa-bars"></i>
            </button>
              <div class="hdr-logo" style="display:flex; align-items:center; gap:8px;">
                  <a href="{{ BASE_URL }}" style="display:flex; align-items:center; text-decoration: none;">
                      <span style="font-weight: 600; font-size: 19px; color: var(--fg-strong, #111827); letter-spacing: 0.5px; padding-left: 14px;">Toba Tech Portal</span>
                  </a>
                  <a href="{{ BASE_URL }}/dashboard/home"
                     class="hdr-icon"
                     style="display:flex !important; align-items:center !important; justify-content:center !important; text-decoration:none;"
                     data-tippy-content="Back to Home Dashboard">
                      <i class="fa-solid fa-house" style="font-size:16px;"></i>
                  </a>
              </div>
            <div class="hdr-spacer"></div>
            <div class="hdr-actions">
                @include('menu::headMenu')
            </div>
        </header>

        <div class="app-body">
            <nav class="side leftpanel">
                @include('menu::menu')
            </nav>
            <main class="content rightpanel {{ $section }}">
                <div class="content-inner primaryContent">
                    @isset($action, $module)
                    @include("$module::$action")
                    @else
                    @yield('content')
                    @endisset
                    <div class="clearfix"></div>
                    @include('global::sections.footer')
                </div>
            </main>
        </div>

    </div><!-- app -->

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>