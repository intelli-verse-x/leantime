@props([
'includeTitle' => true,
'randomImage' => '',
'totalTickets' => 0,
'projectCount' => 0,
'closedTicketsCount' => 0,
'ticketsInGoals' => 0,
'doneTodayCount' => 0,
'totalTodayCount' => 0,
])

<div class="welcome-widget">

    <div class="ww-greeting">
        👋 {{ __('text.hi') }} <strong>{{ session()->get("userdata.name") }}</strong>
    </div>

    <div class="ww-kpi-grid">

        <div class="ww-kpi">
            <div class="ww-kpi-icon" style="background: rgba(99,102,241,0.12); color: #6366f1;">⏱️</div>
            <div class="ww-kpi-body">
                <div class="ww-kpi-num">{{ $doneTodayCount }}/{{ $totalTodayCount }}</div>
                <div class="ww-kpi-lab">{{ __('welcome_widget.timeboxed_completed') }}</div>
            </div>
        </div>

        <div class="ww-kpi">
            <div class="ww-kpi-icon" style="background: rgba(34,197,94,0.12); color: #22c55e;">✅</div>
            <div class="ww-kpi-body">
                <div class="ww-kpi-num">{{ $closedTicketsCount }}</div>
                <div class="ww-kpi-lab">{{ __('welcome_widget.tasks_completed') }}</div>
            </div>
        </div>

        <div class="ww-kpi">
            <div class="ww-kpi-icon" style="background: rgba(245,158,11,0.12); color: #f59e0b;">📋</div>
            <div class="ww-kpi-body">
                <div class="ww-kpi-num">{{ $totalTickets }}</div>
                <div class="ww-kpi-lab">{{ __('welcome_widget.tasks_left') }}</div>
            </div>
        </div>

        <div class="ww-kpi">
            <div class="ww-kpi-icon" style="background: rgba(239,68,68,0.12); color: #ef4444;">🎯</div>
            <div class="ww-kpi-body">
                <div class="ww-kpi-num">{{ $ticketsInGoals }}</div>
                <div class="ww-kpi-lab">{{ __('welcome_widget.goals_contributing_to') }}</div>
            </div>
        </div>

    </div>

    <div class="clear"></div>

    @dispatchEvent('afterWelcomeMessage')

    <div class="clear"></div>

</div>

@dispatchEvent('afterWelcomeMessageBox')
