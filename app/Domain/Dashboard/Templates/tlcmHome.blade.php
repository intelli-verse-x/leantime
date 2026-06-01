@extends($layout)

@section('content')

<div class="page-head" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div class="page-title">
        <h1 style="font-size: 24px; font-weight: 700; color: var(--fg-strong); margin: 0;">My Projects</h1>
        <p style="color: var(--fg-secondary); font-size: 14px; margin: 4px 0 0 0;">Overview of your assigned projects</p>
    </div>
</div>
<div class="tlcm-dashboard">

    {!! $tpl->displayNotification() !!}

    {{-- ── Aggregated KPI Strip ── --}}
    <div class="tlcm-kpi-strip" style="display:grid; grid-template-columns: repeat(6,1fr); gap:14px; margin-bottom:20px; padding: 0 15px;">

        <div class="tlcm-kpi-card">
            <div class="kpi-icon"><i class="fa fa-fw fa-briefcase"></i></div>
            <div class="kpi-value">{{ $totalActiveProjects }}</div>
            <div class="kpi-label">My Projects</div>
        </div>

        <div class="tlcm-kpi-card">
            <div class="kpi-icon"><i class="fa fa-fw fa-list-check"></i></div>
            <div class="kpi-value">{{ $totalOpenTasks }}</div>
            <div class="kpi-label">Open Tasks</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalOverdue > 0 ? 'kpi-warn' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-clock"></i></div>
            <div class="kpi-value">{{ $totalOverdue }}</div>
            <div class="kpi-label">Overdue</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalBlocked > 0 ? 'kpi-danger' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-ban"></i></div>
            <div class="kpi-value">{{ $totalBlocked }}</div>
            <div class="kpi-label">Blocked</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalOpenReqs > 0 ? 'kpi-info' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-inbox"></i></div>
            <div class="kpi-value">{{ $totalOpenReqs }}</div>
            <div class="kpi-label">Open Client Requests</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalPendingReviews > 0 ? 'kpi-review' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-hourglass-half"></i></div>
            <div class="kpi-value">{{ $totalPendingReviews }}</div>
            <div class="kpi-label">Milestones to Review</div>
        </div>

    </div>

    <div class="maincontentinner" style="margin: 0 15px;">

        {{-- ── Top action bar ── --}}
        <div class="tlcm-actionbar">
            <div class="tlcm-actionbar-title">
                <i class="fa fa-fw fa-folder-open" style="opacity:.55;"></i>
                My Projects ({{ $totalActiveProjects }})
            </div>
            <div class="tlcm-actionbar-buttons">
                <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm">
                    <i class="fa fa-calendar-week"></i> Weekly Planning
                </a>
                @if($isCM)
                <a href="{{ BASE_URL }}/projects/showAll" class="btn btn-default btn-sm">
                    <i class="fa fa-briefcase"></i> All Projects
                </a>
                @if($isAdmin)
                <a href="{{ BASE_URL }}/clients/showAll" class="btn btn-default btn-sm">
                    <i class="fa fa-building"></i> Manage Clients
                </a>
                @endif
                <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> New Project
                </a>
                @endif
            </div>
        </div>

        {{-- ── Project rows ── --}}
        @if(empty($cards))
        <div class="tlcm-empty">
            <i class="fa fa-folder-open fa-3x" style="opacity:.35; margin-bottom:12px;"></i>
            <div style="font-size:16px; font-weight:600; margin-bottom:6px;">No projects assigned yet</div>
            <div style="opacity:.6; max-width:480px; margin:0 auto;">
                @if($isCM)
                Create your first project or ask an administrator to assign you to an existing one.
                <div style="margin-top:16px;">
                    <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create Project
                    </a>
                </div>
                @else
                Ask an administrator or a manager to add you to a project.
                @endif
            </div>
        </div>
        @else
        <div class="tlcm-project-list">
            @foreach($cards as $idx => $card)
            @php
            $p = $card['project'];
            $pid = (int) $p['id'];
            $percent = isset($card['progress']['percent']) ? (int) round($card['progress']['percent']) : 0;
            $health = $card['health'];
            $rowId = 'tlcm-row-'.$pid;
            @endphp

            <div class="tlcm-row {{ $health === 'at_risk' ? 'is-atrisk' : '' }}" id="{{ $rowId }}">

                {{-- ── Summary line (one row) ── --}}
                <div class="tlcm-row-summary">

                    {{-- Toggle chevron --}}
                    <button type="button"
                        class="tlcm-toggle"
                        aria-expanded="false"
                        aria-controls="{{ $rowId }}-detail"
                        onclick="tlcmDashboard.toggle('{{ $rowId }}')"
                        title="Show more details">
                        <i class="fa fa-chevron-right"></i>
                    </button>

                    {{-- Project name + client (clickable -> enter project) --}}
                    <a class="tlcm-row-name" href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}">
                        <span class="tlcm-name-text">{{ $p['name'] ?? 'Untitled' }}</span>
                        @if(!empty($p['clientName']))
                        <span class="tlcm-name-client">
                            <i class="fa fa-fw fa-building" style="opacity:.55;"></i> {{ $p['clientName'] }}
                        </span>
                        @endif
                    </a>

                    {{-- Progress inline --}}
                    <div class="tlcm-row-progress">
                        <div class="tlcm-progress-track">
                            <div class="tlcm-progress-fill {{ $health === 'at_risk' ? 'fill-atrisk' : 'fill-ontrack' }}"
                                style="width:{{ $percent }}%;"></div>
                        </div>
                        <span class="tlcm-progress-text">{{ $percent }}%</span>
                    </div>

                    {{-- Stats chips --}}
                    <div class="tlcm-row-stats">
                        <span class="tlcm-chip" title="Open tasks">
                            <i class="fa fa-list-check"></i> {{ $card['openCount'] }}
                        </span>
                        @if($card['overdueCount'] > 0)
                        <span class="tlcm-chip chip-warn" title="Overdue tasks">
                            <i class="fa fa-clock"></i> {{ $card['overdueCount'] }}
                        </span>
                        @endif
                        @if($card['blockedCount'] > 0)
                        <span class="tlcm-chip chip-danger" title="Blocked tasks">
                            <i class="fa fa-ban"></i> {{ $card['blockedCount'] }}
                        </span>
                        @endif
                        @if(count($card['openRequests']) > 0)
                        <span class="tlcm-chip chip-info" title="Open client requests">
                            <i class="fa fa-inbox"></i> {{ count($card['openRequests']) }}
                        </span>
                        @endif
                        @if(count($card['pendingMilestones']) > 0)
                        <span class="tlcm-chip chip-review" title="Milestones awaiting your review">
                            <i class="fa fa-hourglass-half"></i> {{ count($card['pendingMilestones']) }}
                        </span>
                        @endif
                        <span class="tlcm-chip chip-soft" title="Team size">
                            <i class="fa fa-users"></i> {{ count($card['team']) }}
                        </span>
                    </div>

                    {{-- Enter project --}}
                    <a class="tlcm-enter" href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}" title="Enter project">
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

                {{-- ── Expandable detail panel ── --}}
                <div class="tlcm-row-detail" id="{{ $rowId }}-detail" hidden>
                    <div class="tlcm-detail-grid">

                        {{-- Team --}}
                        <div class="tlcm-detail-block">
                            <div class="tlcm-detail-title"><i class="fa fa-fw fa-users"></i> Team ({{ count($card['team']) }})</div>
                            @if(empty($card['team']))
                            <div class="tlcm-detail-empty">No members assigned.</div>
                            @else
                            <div class="tlcm-team-grid">
                                @foreach($card['team'] as $member)
                                <div class="tlcm-team-card">
                                    <div class="tlcm-team-avatar">
                                        {{ strtoupper(substr($member['firstname'] ?? '?', 0, 1)) }}{{ strtoupper(substr($member['lastname'] ?? '', 0, 1)) }}
                                    </div>
                                    <div class="tlcm-team-meta">
                                        <div class="tlcm-team-name">{{ ($member['firstname'] ?? '') }} {{ ($member['lastname'] ?? '') }}</div>
                                        <div class="tlcm-team-role">{{ ucfirst($member['projectRole'] ?? $member['role'] ?? 'Member') }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Recent activity --}}
                        <div class="tlcm-detail-block">
                            <div class="tlcm-detail-title"><i class="fa fa-fw fa-bolt"></i> Recent Activity</div>
                            @if(empty($card['recentActivity']))
                            <div class="tlcm-detail-empty">No recent updates.</div>
                            @else
                            <ul class="tlcm-detail-list">
                                @foreach($card['recentActivity'] as $a)
                                <li>
                                    <span class="activity-who">{{ trim(($a['editorFirstname'] ?? '').' '.($a['editorLastname'] ?? '')) ?: 'Someone' }}</span>
                                    <span class="activity-sep">·</span>
                                    <a href="{{ BASE_URL }}/dashboard/home#/tickets/showTicket/{{ $a['id'] }}" class="activity-what">
                                        {{ \Illuminate\Support\Str::limit($a['headline'], 50) }}
                                    </a>
                                    <span class="activity-when">{{ \Carbon\Carbon::parse($a['modified'])->diffForHumans() }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        {{-- Milestones pending review --}}
                        <div class="tlcm-detail-block">
                            <div class="tlcm-detail-title">
                                <i class="fa fa-fw fa-hourglass-half"></i> Milestones to Review ({{ count($card['pendingMilestones']) }})
                            </div>
                            @if(empty($card['pendingMilestones']))
                            <div class="tlcm-detail-empty">No milestones awaiting review.</div>
                            @else
                            <ul class="tlcm-detail-list">
                                @foreach($card['pendingMilestones'] as $ms)
                                <li>
                                    <div class="tlcm-milestone-review-row">
                                        <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}" class="tlcm-milestone-link">
                                            <i class="fa fa-flag" style="opacity:.5;"></i>
                                            {{ \Illuminate\Support\Str::limit($ms['headline'], 40) }}
                                            @if(!empty($ms['editTo']) && $ms['editTo'] !== '0000-00-00 00:00:00')
                                            <span class="req-when">Due {{ \Carbon\Carbon::parse($ms['editTo'])->format('M j') }}</span>
                                            @endif
                                        </a>
                                        <div class="tlcm-milestone-actions">
                                            <form method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}?id={{ $ms['id'] }}" style="display:inline;">
                                                <button type="submit" name="markComplete" value="1"
                                                    class="btn-inline btn-inline-approve"
                                                    title="Approve & Complete">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}"
                                                class="btn-inline btn-inline-view" title="Open to review / reject">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        {{-- Awaiting response --}}
                        <div class="tlcm-detail-block">
                            <div class="tlcm-detail-title">
                                <i class="fa fa-fw fa-inbox"></i> Awaiting My Response ({{ count($card['openRequests']) }})
                            </div>
                            @if(empty($card['openRequests']))
                            <div class="tlcm-detail-empty">All client requests are handled.</div>
                            @else
                            <ul class="tlcm-detail-list">
                                @foreach(array_slice($card['openRequests'], 0, 5) as $req)
                                <li>
                                    <a href="{{ BASE_URL }}/clientportal/adminRequests?projectId={{ $pid }}#request-{{ $req['id'] }}">
                                        <span class="req-title">{{ \Illuminate\Support\Str::limit($req['title'] ?? 'Untitled', 55) }}</span>
                                        <span class="req-when">
                                            @if(!empty($req['createdAt']))
                                            {{ \Carbon\Carbon::parse($req['createdAt'])->diffForHumans() }}
                                            @endif
                                        </span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                    </div>

                    {{-- Quick links --}}
                    <div class="tlcm-detail-actions">
                        <a href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-folder-open"></i> Enter Project
                        </a>
                        <a href="{{ BASE_URL }}/clientportal/adminRequests?projectId={{ $pid }}" class="btn btn-default btn-sm">
                            <i class="fa fa-inbox"></i> Client Requests
                        </a>
                        <a href="{{ BASE_URL }}/tickets/roadmap?projectId={{ $pid }}" class="btn btn-default btn-sm">
                            <i class="fa fa-chart-gantt"></i> Timeline
                        </a>
                        @if($isCM)
                            <a href="{{ BASE_URL }}/projects/delProject?id={{ $pid }}"
                               class="btn btn-sm tlcm-btn-danger"
                               onclick="return confirm('Delete project &quot;{{ addslashes($p['name'] ?? 'this project') }}&quot;?\n\nYou will be taken to a confirmation page. This action cannot be undone once confirmed.');">
                                <i class="fa fa-trash"></i> Delete Project
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

<style>
    /* -- Toba Tech Premium Styles for Dashboard -- */
    .tlcm-dashboard { padding: 0 !important; }
    
    /* KPI Strip */
    .tlcm-kpi-strip {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
        padding: 0 15px;
    }
    @media (max-width: 1200px) {
        .tlcm-kpi-strip {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .tlcm-kpi-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .tlcm-kpi-strip {
            grid-template-columns: 1fr;
        }
    }

    .tlcm-kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }
    .tlcm-kpi-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
        border-color: var(--border-strong);
    }
    .tlcm-kpi-card .kpi-icon {
        font-size: 20px;
        color: var(--accent1);
        margin-bottom: 8px;
    }
    .tlcm-kpi-card .kpi-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--fg-strong);
        line-height: 1;
    }
    .tlcm-kpi-card .kpi-label {
        font-size: 11px;
        color: var(--fg-secondary);
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 4px;
        letter-spacing: 0.5px;
    }
    .tlcm-kpi-card.kpi-warn { border-left: 4px solid #f59e0b; }
    .tlcm-kpi-card.kpi-warn .kpi-icon { color: #f59e0b; }
    .tlcm-kpi-card.kpi-danger { border-left: 4px solid #ef4444; }
    .tlcm-kpi-card.kpi-danger .kpi-icon { color: #ef4444; }
    .tlcm-kpi-card.kpi-info { border-left: 4px solid #3b82f6; }
    .tlcm-kpi-card.kpi-info .kpi-icon { color: #3b82f6; }
    .tlcm-kpi-card.kpi-review { border-left: 4px solid #e67e22; }
    .tlcm-kpi-card.kpi-review .kpi-icon { color: #e67e22; }

    /* Action bar */
    .tlcm-actionbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }
    .tlcm-actionbar-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--fg-strong);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tlcm-actionbar-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Project row container */
    .tlcm-row {
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 16px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .tlcm-row:hover {
        border-color: var(--border-strong);
        box-shadow: var(--shadow-md);
    }
    .tlcm-row.is-atrisk {
        border-left: 4px solid #ef4444;
    }

    /* Summary Header Line */
    .tlcm-row-summary {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 18px 24px;
        width: 100%;
        background: var(--bg-surface);
        flex-wrap: wrap;
    }
    @media (max-width: 992px) {
        .tlcm-row-summary {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }
        .tlcm-row-progress {
            width: 100% !important;
        }
        .tlcm-row-stats {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        .tlcm-enter {
            align-self: flex-end;
        }
    }

    /* Chevron Toggle */
    .tlcm-toggle {
        background: transparent;
        border: none;
        color: var(--fg-secondary);
        font-size: 14px;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
        width: 28px;
        height: 28px;
        border-radius: 6px;
    }
    .tlcm-toggle:hover {
        background: var(--bg-hover);
        color: var(--fg-strong);
    }
    .tlcm-row.open .tlcm-toggle,
    .tlcm-toggle[aria-expanded="true"] {
        transform: rotate(90deg);
    }

    /* Name & Client */
    .tlcm-row-name {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 0;
        text-decoration: none !important;
    }
    .tlcm-name-text {
        font-size: 16px;
        font-weight: 600;
        color: var(--fg-strong);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.15s ease;
    }
    .tlcm-row-name:hover .tlcm-name-text {
        color: var(--accent1);
    }
    .tlcm-name-client {
        font-size: 13px;
        color: var(--fg-secondary);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Progress track */
    .tlcm-row-progress {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 200px;
        flex-shrink: 0;
    }
    .tlcm-progress-track {
        background: var(--bg-inset);
        height: 8px;
        border-radius: 4px;
        flex: 1;
        overflow: hidden;
    }
    .tlcm-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    .fill-ontrack {
        background: #22c55e;
    }
    .fill-atrisk {
        background: #ef4444;
    }
    .tlcm-progress-text {
        font-size: 13px;
        font-weight: 600;
        color: var(--fg-secondary);
        min-width: 36px;
        text-align: right;
    }

    /* Stats Chips */
    .tlcm-row-stats {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .tlcm-chip {
        background: var(--bg-inset);
        border: 1px solid var(--border-default);
        color: var(--fg-secondary);
        font-weight: 500;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .tlcm-chip i {
        font-size: 12px;
    }
    .tlcm-chip.chip-warn {
        background: #fffbeb;
        color: #d97706;
        border-color: #fde68a;
    }
    .tlcm-chip.chip-danger {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .tlcm-chip.chip-info {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .tlcm-chip.chip-review {
        background: #fff7ed;
        color: #ea580c;
        border-color: #ffedd5;
    }
    .tlcm-chip.chip-soft {
        background: var(--bg-inset);
        color: var(--fg-secondary);
        border-color: var(--border-default);
    }

    /* Enter Arrow Link */
    .tlcm-enter {
        background: var(--bg-inset);
        border: 1px solid var(--border-default);
        color: var(--fg-secondary);
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .tlcm-enter:hover {
        background: var(--accent1);
        color: #fff;
        border-color: var(--accent1);
    }

    /* Expandable Detail Panel */
    .tlcm-row-detail {
        background: var(--bg-page);
        border-top: 1px solid var(--border-default);
        padding: 24px;
        animation: slideDown 0.25s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 4-Column Detail Grid */
    .tlcm-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }
    @media (max-width: 1200px) {
        .tlcm-detail-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .tlcm-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .tlcm-detail-block {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .tlcm-detail-title {
        color: var(--fg-secondary);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.75px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .tlcm-detail-title i {
        color: var(--accent1);
        opacity: 0.8;
    }
    .tlcm-detail-empty {
        font-size: 13px;
        color: var(--fg-muted);
        font-style: italic;
        padding: 12px 14px;
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 8px;
        text-align: center;
    }

    /* Team Grid & Cards */
    .tlcm-team-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tlcm-team-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 8px;
        padding: 10px 14px;
        transition: border-color 0.15s ease;
    }
    .tlcm-team-card:hover {
        border-color: var(--border-strong);
    }
    .tlcm-team-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--accent1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
        flex-shrink: 0;
    }
    .tlcm-team-meta {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .tlcm-team-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--fg-strong);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tlcm-team-role {
        font-size: 11px;
        color: var(--fg-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Detail lists (Activity, Milestones, Awaiting response) */
    .tlcm-detail-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tlcm-detail-list li {
        font-size: 13px;
        line-height: 1.4;
        color: var(--fg-secondary);
        padding: 10px 14px;
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 8px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .tlcm-detail-list li:hover {
        background: var(--bg-hover);
        border-color: var(--border-strong);
    }

    /* Activity Items */
    .activity-who {
        font-weight: 600;
        color: var(--fg-strong);
    }
    .activity-sep {
        color: var(--fg-muted);
        margin: 0 4px;
    }
    .activity-what {
        color: var(--accent1);
        text-decoration: none;
        font-weight: 500;
    }
    .activity-what:hover {
        text-decoration: underline;
    }
    .activity-when {
        display: block;
        font-size: 11px;
        color: var(--fg-muted);
        margin-top: 4px;
    }

    /* Milestones review list */
    .tlcm-milestone-review-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
    }
    .tlcm-milestone-link {
        color: var(--fg-strong);
        font-weight: 500;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
        min-width: 0;
    }
    .tlcm-milestone-link:hover {
        color: var(--accent1);
    }
    .tlcm-milestone-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .btn-inline {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--border-default);
        background: var(--bg-surface);
        color: var(--fg-secondary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.15s ease;
    }
    .btn-inline:hover {
        background: var(--bg-hover);
        color: var(--fg-strong);
    }
    .btn-inline-approve:hover {
        background: #dcfce7;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .btn-inline-view:hover {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    /* Awaiting Client Response links */
    .tlcm-detail-list li a {
        display: flex;
        flex-direction: column;
        gap: 4px;
        text-decoration: none !important;
        color: inherit;
        width: 100%;
    }
    .req-title {
        font-weight: 500;
        color: var(--fg-strong);
    }
    .req-title:hover {
        color: var(--accent1);
    }
    .req-when {
        font-size: 11px;
        color: var(--fg-muted);
    }

    /* Bottom Quick Links Actions Footer */
    .tlcm-detail-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1px solid var(--border-default);
        flex-wrap: wrap;
    }
    .tlcm-btn-danger {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
    }
    .tlcm-btn-danger:hover {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }

    /* Empty state */
    .tlcm-empty {
        text-align: center;
        padding: 48px 24px;
        color: var(--fg-secondary);
        background: var(--bg-surface);
        border: 1px solid var(--border-default);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }
</style>
<script>
    var tlcmDashboard = (function() {
        function toggle(rowId) {
            var row = document.getElementById(rowId);
            if (!row) return;
            var btn = row.querySelector('.tlcm-toggle');
            var detail = row.querySelector('.tlcm-row-detail');
            if (!btn || !detail) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            if (open) {
                detail.setAttribute('hidden', '');
                row.classList.remove('open');
            } else {
                detail.removeAttribute('hidden');
                row.classList.add('open');
            }
        }
        return {
            toggle: toggle
        };
    })();
</script>

@endsection
