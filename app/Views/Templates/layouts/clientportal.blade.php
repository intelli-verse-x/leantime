<!DOCTYPE html>
<html dir="{{ __('language.direction') }}" lang="{{ __('language.code') }}" data-theme="{{ app(\Leantime\Core\UI\Theme::class)->getColorMode() }}">

<head>
    @include('global::sections.header')
    @stack('styles')
    <style>
        /* ── Client Portal layout overrides ── */

        /* Suppress the teal header-gradient that bleeds on the right */
        .mainwrapper.clientportal {
            background-color: var(--bg-page, #f5f7f9) !important;
            background-image: none !important;
        }

        /* Full-width body — no sidebar flex partner */
        .mainwrapper.clientportal .app-body {
            display: block;
        }

        /* Stretch the content panel to the full viewport width,
           then cap and center it at a comfortable reading width */
        .mainwrapper.clientportal .rightpanel {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-top: 80px; /* clear the fixed 50px header + breathing room */
        }

        .mainwrapper.clientportal .primaryContent {
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 24px 40px;
        }

        /* Hide the hamburger menu toggle — clients have no sidebar to toggle */
        .mainwrapper.clientportal .barmenu {
            display: none !important;
        }

        /* Remove the background image overlay on the right panel */
        .mainwrapper.clientportal .rightpanel::before {
            display: none !important;
        }
    </style>
</head>

<body class="" hx-ext="preload">

    @include('global::sections.appAnnouncement')

    <div class="mainwrapper clientportal">

        <header class="hdr">
            <div class="hdr-logo" style="display:flex; align-items:center; gap:8px; padding-left:16px;">
                <a href="{{ BASE_URL }}" style="display:flex; align-items:center; text-decoration:none;">
                    <span style="font-weight:600; font-size:19px; color:var(--fg-strong,#111827); letter-spacing:0.5px;">Toba Tech Portal</span>
                </a>
            </div>
            <div class="hdr-spacer"></div>
            <div class="hdr-actions">
                @include('menu::headMenu')
            </div>
        </header>

        <div class="app-body">
            <main class="content rightpanel {{ $section ?? '' }}">
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

    </div><!-- clientportal -->

    @include('global::sections.pageBottom')
    @stack('scripts')
    @include('help::helpermodal')
</body>

</html>
