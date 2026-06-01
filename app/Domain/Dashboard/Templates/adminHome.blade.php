@extends($layout)

@section('content')

<style>
/* ──────────────────────────────────────────
   Admin Dashboard — Scoped Styles
   (all classes are local to this template)
────────────────────────────────────────── */

/* Alias theme vars used in the inline styles to real values */
:root {
    --bg-surface:      var(--secondary-background, #fff);
    --border-default:  var(--main-border-color, #e5e7eb);
    --fg-strong:       var(--primary-font-color, #111827);
    --fg-secondary:    #6b7280;
    --shadow-sm:       0 1px 3px rgba(0,0,0,.08);
}

/* ── KPI widgets (older class names used in the HTML) ── */
.kpi {
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    display: flex; align-items: center; gap: 16px;
}
.kpi-ico {
    width: 48px; height: 48px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    flex-shrink: 0;
}
.kpi-num { font-size: 28px; font-weight: 700; color: var(--primary-font-color, #111827); line-height: 1; }
.kpi-cap { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }
.kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }


/* ── Page header ── */
.page-head { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; }
.page-title h1 { font-size: 24px; font-weight: 700; color: var(--fg-strong, #111827); margin: 0; }
.page-title p  { color: var(--fg-secondary, #6b7280); font-size: 14px; margin: 4px 0 0 0; }

/* ── KPI row ── */
.admin-kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.admin-kpi {
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    display: flex; align-items: center; gap: 16px;
}
.admin-kpi-icon {
    width: 48px; height: 48px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    flex-shrink: 0;
}
.admin-kpi-num  { font-size: 28px; font-weight: 700; color: var(--fg-strong, #111827); line-height: 1; }
.admin-kpi-cap  { font-size: 11px; color: var(--fg-secondary, #6b7280); font-weight: 600;
                  text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }

/* ── Toolbar card ── */
.admin-toolbar-card {
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    padding: 20px 24px;
    margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    flex-wrap: wrap;
}
.admin-toolbar-card input[type="text"] {
    padding: 9px 14px; border-radius: 8px;
    border: 1px solid var(--main-border-color, #d1d5db);
    background: var(--primary-background, #f9fafb);
    color: var(--fg-strong, #111827); font-size: 14px;
    min-width: 260px; outline: none; transition: border-color .15s;
}
.admin-toolbar-card input[type="text"]:focus { border-color: var(--accent1); }

/* ── Segmented filter buttons ── */
.segmented { display: flex; gap: 4px; background: var(--primary-background, #f3f4f6); border-radius: 8px; padding: 3px; }
.seg {
    padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer;
    font-size: 13px; font-weight: 500; color: var(--fg-secondary, #6b7280);
    background: transparent; transition: background .15s, color .15s;
}
.seg:hover  { background: rgba(0,0,0,.06); color: var(--fg-strong, #111827); }
.seg.active { background: var(--secondary-background, #fff); color: var(--accent1);
              box-shadow: 0 1px 3px rgba(0,0,0,.12); font-weight: 600; }

/* ── Project cards grid ── */
.admin-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px; }

/* ── Individual project card ── */
.proj-card {
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex; flex-direction: column;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.proj-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.12); transform: translateY(-2px); }

/* card body */
.pc-body { padding: 20px; flex: 1; }

/* card header row */
.pc-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.pc-name { font-size: 16px; font-weight: 700; color: var(--fg-strong, #111827); margin: 0 0 4px 0; line-height: 1.3; }
.pc-client { font-size: 12px; color: var(--fg-secondary, #6b7280); display: flex; align-items: center; gap: 4px; }
.pc-client i { opacity: .6; }

/* progress */
.pc-prog-label { display: flex; justify-content: space-between; font-size: 12px;
                 color: var(--fg-secondary, #6b7280); margin-bottom: 6px; }
.pc-prog-label .pct { font-weight: 600; color: var(--fg-strong, #111827); }
.progress {
    height: 6px; border-radius: 99px;
    background: var(--main-border-color, #e5e7eb); overflow: hidden; margin-bottom: 12px;
}
.progress span {
    display: block; height: 100%;
    background: var(--accent1);
    border-radius: 99px; transition: width .4s ease;
}

/* due date */
.pc-due { font-size: 12px; color: #ef4444; margin-bottom: 12px; font-weight: 500; }

/* members row */
.pc-members { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.pc-members > span { font-size: 12px; color: var(--fg-secondary, #6b7280); }

/* avatar stack */
.avatar-stack { display: flex; }
.avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--accent1); color: #fff;
    font-size: 10px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--secondary-background, #fff);
    margin-left: -6px;
}
.avatar:first-child { margin-left: 0; }
.avatar.more { background: var(--main-border-color, #e5e7eb); color: var(--fg-secondary, #6b7280); }

/* activity section */
.pc-activity {
    border-top: 1px solid var(--main-border-color, #e5e7eb);
    padding: 14px 20px;
    background: var(--primary-background, #f9fafb);
}
.eyebrow { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
           color: var(--fg-secondary, #6b7280); display: flex; align-items: center; gap: 6px; }
.pc-act-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 10px; padding: 8px 0;
    border-bottom: 1px solid var(--main-border-color, #e5e7eb);
}
.pc-act-row:last-child { border-bottom: none; padding-bottom: 0; }
.pc-act-row .txt { font-size: 13px; color: var(--fg-strong, #111827); line-height: 1.4; flex: 1; }
.pc-act-row .tm  { font-size: 11px; color: var(--fg-secondary, #6b7280); margin-top: 2px; }
.pc-act-row .ai  { color: var(--fg-secondary, #6b7280); opacity: .5; font-size: 13px; flex-shrink: 0; margin-top: 3px; }

/* card footer */
.pc-foot {
    display: flex; gap: 4px;
    padding: 12px 16px;
    border-top: 1px solid var(--main-border-color, #e5e7eb);
    background: var(--primary-background, #f9fafb);
}
.pc-foot a {
    flex: 1; text-align: center;
    padding: 7px 10px; border-radius: 7px;
    font-size: 13px; font-weight: 500;
    color: var(--accent1); text-decoration: none;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
    display: flex; align-items: center; justify-content: center; gap: 5px;
}
.pc-foot a:hover {
    background: rgba(37,99,235,.07);
    border-color: var(--accent1);
}

/* ── Badges ── */
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 6px;
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    white-space: nowrap; flex-shrink: 0;
}
.badge-blue      { background: rgba(37,99,235,.12);   color: #1d4ed8; }
.badge-amber     { background: rgba(245,158,11,.14);   color: #b45309; }
.badge-soft-red  { background: rgba(239,68,68,.12);    color: #b91c1c; }
.badge-red       { background: rgba(239,68,68,.85);    color: #fff; }

/* ── Empty state ── */
.admin-empty { grid-column: 1/-1; text-align: center; padding: 64px 0; }
.admin-empty i { font-size: 48px; color: var(--fg-secondary, #9ca3af); opacity: .45; margin-bottom: 14px; display: block; }
.admin-empty p { color: var(--fg-secondary, #6b7280); font-size: 15px; }
</style>

<div class="page-head" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div class="page-title">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--fg-strong); margin: 0;">Admin Dashboard</h1>
        <p style="color: var(--fg-secondary); font-size: 14px; margin: 4px 0 0 0;">Company overview and system metrics</p>
    </div>
    <div class="page-actions">
        <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary" style="background: var(--accent1); color: #fff; border-radius: 6px; padding: 8px 16px; text-decoration: none; font-size: 14px; font-weight: 500;">
            <i class="fa-solid fa-plus" style="margin-right: 6px;"></i> New Project
        </a>
    </div>
</div>

{!! $tpl->displayNotification() !!}

<div class="kpi-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    
    <div class="kpi" style="background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px;">
        <div class="kpi-ico" style="width: 48px; height: 48px; border-radius: 10px; background: rgba(37,99,235,0.1); color: var(--accent1); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-briefcase"></i>
        </div>
        <div>
            <div class="kpi-num" style="font-size: 28px; font-weight: 700; color: var(--fg-strong); line-height: 1;">{{ $totalActiveProjects }}</div>
            <div class="kpi-cap" style="font-size: 12px; color: var(--fg-secondary); font-weight: 500; text-transform: uppercase; margin-top: 4px;">Active Projects</div>
        </div>
    </div>

    <div class="kpi" style="background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px; {{ $totalOverdue > 0 ? 'border-left: 4px solid #f59e0b;' : '' }}">
        <div class="kpi-ico" style="width: 48px; height: 48px; border-radius: 10px; background: rgba(245,158,11,0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div class="kpi-num" style="font-size: 28px; font-weight: 700; color: var(--fg-strong); line-height: 1;">{{ $totalOverdue }}</div>
            <div class="kpi-cap" style="font-size: 12px; color: var(--fg-secondary); font-weight: 500; text-transform: uppercase; margin-top: 4px;">Overdue Tasks</div>
        </div>
    </div>

    <div class="kpi" style="background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px; {{ $totalBlocked > 0 ? 'border-left: 4px solid #ef4444;' : '' }}">
        <div class="kpi-ico" style="width: 48px; height: 48px; border-radius: 10px; background: rgba(239,68,68,0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <div class="kpi-num" style="font-size: 28px; font-weight: 700; color: var(--fg-strong); line-height: 1;">{{ $totalBlocked }}</div>
            <div class="kpi-cap" style="font-size: 12px; color: var(--fg-secondary); font-weight: 500; text-transform: uppercase; margin-top: 4px;">Blocked Tasks</div>
        </div>
    </div>

    <div class="kpi" style="background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px; {{ $openClientRequests > 0 ? 'border-left: 4px solid var(--accent1);' : '' }}">
        <div class="kpi-ico" style="width: 48px; height: 48px; border-radius: 10px; background: rgba(37,99,235,0.1); color: var(--accent1); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-inbox"></i>
        </div>
        <div>
            <div class="kpi-num" style="font-size: 28px; font-weight: 700; color: var(--fg-strong); line-height: 1;">{{ $openClientRequests }}</div>
            <div class="kpi-cap" style="font-size: 12px; color: var(--fg-secondary); font-weight: 500; text-transform: uppercase; margin-top: 4px;">Open Requests</div>
        </div>
    </div>

</div>

<div class="card" style="background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 12px; box-shadow: var(--shadow-sm); padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
          <input type="text" id="admin-project-search" placeholder="Search projects or clients..." style="width: 300px; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border-default); background: var(--bg-surface); color: var(--fg-strong);" onkeyup="adminDashboard.filterCards()" />
          
          <div class="segmented">
            <button class="seg active" data-health="all" onclick="adminDashboard.setFilter(this,'all')">All</button>
            <button class="seg" data-health="at_risk" onclick="adminDashboard.setFilter(this,'at_risk')">At Risk</button>
            <button class="seg" data-health="idle" onclick="adminDashboard.setFilter(this,'idle')">Idle</button>
            <button class="seg" data-health="on_track" onclick="adminDashboard.setFilter(this,'on_track')">On Track</button>
          </div>
      </div>

    <div id="admin-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
                @forelse($projectCards as $card)
            @php
                $project  = $card['project'];
                $progress = $card['progress'];
                $team     = $card['team'];
                $health   = $card['health'];
                $percent  = isset($progress['percent']) ? (int) round($progress['percent']) : 0;

                $badgeClass = match($health) {
                    'at_risk' => 'badge-soft-red',
                    'idle'    => 'badge-amber',
                    default   => 'badge-blue',
                };
                $badgeText = match($health) {
                    'at_risk' => '<i class="fa-solid fa-triangle-exclamation"></i> AT RISK',
                    'idle'    => '<i class="fa-solid fa-pause"></i> IDLE',
                    default   => 'ACTIVE',
                };
            @endphp

            <div class="proj-card admin-project-card" data-health="{{ $health }}" data-name="{{ strtolower($project['name']) }}" data-client="{{ strtolower($project['clientName'] ?? '') }}">
                <div class="pc-body">
                    <div class="pc-head">
                        <div>
                            <h3 class="pc-name">{{ $project['name'] }}</h3>
                            @if(!empty($project['clientName']))
                                <span class="pc-client"><i class="fa-solid fa-building"></i>{{ $project['clientName'] }}</span>
                            @endif
                        </div>
                        <span class="badge {{ $badgeClass }}">{!! $badgeText !!}</span>
                    </div>

                    <div class="pc-prog-label"><span>Overall Progress</span><span class="pct">{{ $percent }}%</span></div>
                    <div class="progress"><span style="width: {{ $percent }}%"></span></div>

                    @if(!empty($project['end']) && $project['end'] !== '0000-00-00')
                        <div class="pc-due">Due: {{ \Carbon\Carbon::parse($project['end'])->format('M j, Y') }}</div>
                    @endif

                    <div class="pc-members">
                        <div class="avatar-stack">
                            @foreach(array_slice($team, 0, 4) as $member)
                                <div class="avatar" title="{{ ($member['firstname'] ?? '') }} {{ ($member['lastname'] ?? '') }}">
                                    {{ strtoupper(substr($member['firstname'] ?? '?', 0, 1)) }}{{ strtoupper(substr($member['lastname'] ?? '', 0, 1)) }}
                                </div>
                            @endforeach
                            @if(count($team) > 4)
                                <div class="avatar more">+{{ count($team) - 4 }}</div>
                            @endif
                        </div>
                        <span>{{ count($team) }} member{{ count($team) !== 1 ? 's' : '' }}</span>

                        @if(($card['overdueCount'] ?? 0) > 0 || ($card['blockedCount'] ?? 0) > 0)
                            <div style="margin-left: auto; display: flex; gap: 8px;">
                                @if(($card['overdueCount'] ?? 0) > 0)
                                    <span class="badge badge-soft-red"><i class="fa-solid fa-clock"></i> {{ $card['overdueCount'] }}</span>
                                @endif
                                @if(($card['blockedCount'] ?? 0) > 0)
                                    <span class="badge badge-red"><i class="fa-solid fa-ban"></i> {{ $card['blockedCount'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty($card['recentActivity']))
                    <div class="pc-activity">
                        <span class="eyebrow"><i class="fa-solid fa-bolt"></i> Recent Activity</span>
                        <div style="margin-top: 12px">
                            @foreach(array_slice($card['recentActivity'], 0, 2) as $activity)
                                <div class="pc-act-row">
                                    <div class="txt">
                                        <div><b>{{ $activity['editorFirstname'] }} {{ $activity['editorLastname'] }}</b> {{ \Illuminate\Support\Str::limit($activity['headline'] ?? '', 45) }}</div>
                                        <div class="tm">{{ \Carbon\Carbon::parse($activity['modified'])->diffForHumans() }}</div>
                                    </div>
                                    <i class="fa-solid fa-clock-rotate-left ai"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pc-foot">
                    <a href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}"><i class="fa-solid fa-eye"></i> View Project</a>
                    <a href="{{ BASE_URL }}/tickets/showKanban"><i class="fa-solid fa-list-check"></i> Boards</a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 0;">
                <i class="fa-solid fa-briefcase" style="font-size: 48px; color: var(--fg-secondary); opacity: 0.5; margin-bottom: 16px;"></i>
                <p style="color: var(--fg-secondary); font-size: 15px;">No active projects found.</p>
                <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary" style="margin-top: 16px;">Create your first project</a>
            </div>
        @endforelse
    </div>
</div>

<script>
var adminDashboard = (function () {
    var activeFilter = 'all';

    function setFilter(btn, filter) {
        activeFilter = filter;
        document.querySelectorAll('.seg').forEach(function (c) {
            if (c.dataset.health === filter) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });
        applyFilters();
    }

    function filterCards() {
        applyFilters();
    }

    function applyFilters() {
        var search = (document.getElementById('admin-project-search').value || '').toLowerCase().trim();
        document.querySelectorAll('.admin-project-card').forEach(function (card) {
            var matchHealth = activeFilter === 'all' || card.dataset.health === activeFilter;
            var matchSearch = !search
                || (card.dataset.name || '').includes(search)
                || (card.dataset.client || '').includes(search);
            card.style.display = (matchHealth && matchSearch) ? 'flex' : 'none';
        });
    }

    return { setFilter: setFilter, filterCards: filterCards };
})();
</script>

@endsection

