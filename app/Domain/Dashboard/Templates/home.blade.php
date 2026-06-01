@extends($layout)

@section('content')

<div class="page-head" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div class="page-title">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--fg-strong); margin: 0;">My Dashboard</h1>
        <p style="color: var(--fg-secondary); font-size: 14px; margin: 4px 0 0 0;">Customizable widget overview</p>
    </div>
</div>

<div class="maincontent" id="gridBoard" style="margin-top:0px; opacity:0;">

    {!! $tpl->displayNotification() !!}

    <div class="grid-stack">

        @foreach($dashboardGrid as $widget)

        @if($widget->id === 'welcome')
        <x-widgets::moveableWidget
            gs-x="{{ $widget->gridX }}"
            gs-y="{{ $widget->gridY }}"
            gs-h="{{ $widget->gridHeight }}"
            gs-w="{{ $widget->gridWidth }}"
            gs-min-w="{{ $widget->gridMinWidth }}"
            gs-min-h="{{ $widget->gridMinHeight }}"
            isNew="{{ isset($widget->isNew) ? 'true' : 'false' }}"
            background="{{ $widget->widgetBackground }}"
            noTitle="{{ $widget->noTitle }}"
            name="{{ $widget->name }}"
            :fixed="true"
            alwaysVisible="{{ $widget->alwaysVisible }}"
            id="widget_wrapper_{{ $widget->id }}"
            gs-no-resize="true"
            gs-no-move="true">
            <div hx-get="{{$widget->widgetUrl }}"
                hx-trigger="revealed"
                id="{{ $widget->id }}"
                class="tw-h-full"
                hx-swap="#{{ $widget->id }}">
                <x-global::loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
            </div>
        </x-widgets::moveableWidget>
        @else
        <x-widgets::moveableWidget
            gs-x="{{ $widget->gridX }}"
            gs-y="{{ $widget->gridY }}"
            gs-h="{{ $widget->gridHeight }}"
            gs-w="{{ $widget->gridWidth }}"
            gs-min-w="{{ $widget->gridMinWidth }}"
            gs-min-h="{{ $widget->gridMinHeight }}"
            isNew="{{ isset($widget->isNew) ? 'true' : 'false' }}"
            background="{{ $widget->widgetBackground }}"
            noTitle="{{ $widget->noTitle }}"
            name="{{ $widget->name }}"
            :fixed="(empty($widget->fixed) ? false : true )"
            alwaysVisible="{{ $widget->alwaysVisible }}"
            id="widget_wrapper_{{ $widget->id }}">
            <div hx-get="{{$widget->widgetUrl }}"
                hx-trigger="revealed"
                id="{{ $widget->id }}"
                class="tw-h-full"
                hx-swap="#{{ $widget->id }}">
                <x-global::loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
            </div>
        </x-widgets::moveableWidget>
        @endif

        @endforeach
    </div>
</div>

<style>
    /* Style GridStack items to look like Toba Tech Cards */
    .grid-stack-item-content {
        background: var(--bg-surface) !important;
        border: 1px solid var(--border-default) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow-sm) !important;
        transition: box-shadow 0.15s;
        color: var(--fg-strong);
    }

    .grid-stack-item-content:hover {
        box-shadow: var(--shadow-md) !important;
    }

    .ui-draggable-dragging .grid-stack-item-content {
        box-shadow: var(--shadow-lg) !important;
    }

    .widget-header {
        border-bottom: 1px solid var(--border-default) !important;
        padding: 12px 16px !important;
    }

    .widget-header h3 {
        font-size: 14px !important;
        font-weight: 600 !important;
        color: var(--fg-strong) !important;
    }

    /* Make welcome/greeting widget card container transparent to let KPI cards float independently */
    .grid-stack-item:has(.welcome-widget) .grid-stack-item-content,
    .grid-stack-item:has(.welcome-widget) .grid-stack-item-content:hover,
    [id^="widget_wrapper_"]:has(.welcome-widget) .grid-stack-item-content,
    [id^="widget_wrapper_"]:has(.welcome-widget) .grid-stack-item-content:hover {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        overflow: visible !important;
    }
</style>

<script>
    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function() {

        leantime.widgetController.initGrid();

        // Ensure welcome widget is tall enough to display KPI cards properly.
        // Force all users to h=9 (270px) so greeting + cards show without any scroll.
        setTimeout(function() {
            var welcomeEl = document.getElementById('widget_wrapper_welcome');
            if (welcomeEl) {
                var currentH = parseInt(welcomeEl.getAttribute('gs-h') || '3', 10);
                if (currentH < 9) {
                    var gs = document.querySelector('.grid-stack') && document.querySelector('.grid-stack').gridstack;
                    if (gs) {
                        gs.update(welcomeEl, {
                            h: 9,
                            minH: 7
                        });
                        leantime.widgetController.saveGrid();
                    }
                }
            }
        }, 300);

        @php(session(["usersettings.modals.homeDashboardTour" => 1]))

    });
</script>

@endsection
