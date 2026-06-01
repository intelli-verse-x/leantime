@extends($layout)

@section('content')

<style>
/* ── Client Portal Dashboard ── */

/* The global .maincontent has margin-top:-95px to pull it up over the
   standard .pageheader. We're not using pageheader here, so reset it. */
.maincontent {
    margin-top: 0 !important;
}

.cp-welcome {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
    padding: 24px 28px;
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
}
.cp-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent1, #0c4a6e);
    flex-shrink: 0;
    background: var(--primary-background, #f3f4f6);
}
.cp-avatar-fallback {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: var(--accent1, #0c4a6e);
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 3px solid var(--accent1, #0c4a6e);
}
.cp-welcome-text h1 {
    font-size: 22px; font-weight: 700;
    color: var(--primary-font-color, #111827);
    margin: 0 0 4px 0;
}
.cp-welcome-text p {
    color: #6b7280; font-size: 14px; margin: 0;
}

.cp-section-title {
    font-size: 15px; font-weight: 700;
    color: var(--primary-font-color, #111827);
    margin: 0 0 16px 0;
    display: flex; align-items: center; gap: 8px;
}
.cp-section-title i { color: var(--accent1, #0c4a6e); }

.cp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 18px;
}

.cp-card {
    background: var(--secondary-background, #fff);
    border: 1px solid var(--main-border-color, #e5e7eb);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    display: flex; flex-direction: column;
    transition: box-shadow .2s, transform .2s;
}
.cp-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.11); transform: translateY(-2px); }

.cp-card-body { padding: 20px; flex: 1; }

.cp-card-head {
    display: flex; justify-content: space-between;
    align-items: flex-start; gap: 12px; margin-bottom: 16px;
}
.cp-card-name {
    font-size: 16px; font-weight: 700;
    color: var(--primary-font-color, #111827);
    margin: 0; line-height: 1.3;
}
.cp-card-name i { color: var(--accent1, #0c4a6e); margin-right: 6px; }

.cp-prog-label {
    display: flex; justify-content: space-between;
    font-size: 12px; color: #6b7280; margin-bottom: 6px;
}
.cp-prog-label .pct { font-weight: 700; color: var(--primary-font-color, #111827); }

.cp-progress-bar {
    height: 7px; border-radius: 99px;
    background: var(--primary-background, #e5e7eb);
    overflow: hidden; margin-bottom: 14px;
}
.cp-progress-bar span {
    display: block; height: 100%;
    background: var(--accent1, #0c4a6e);
    border-radius: 99px; transition: width .4s ease;
}

.cp-meta {
    display: flex; gap: 16px; flex-wrap: wrap;
    font-size: 12px; color: #6b7280;
}
.cp-meta i { margin-right: 4px; }
.cp-meta .hi { color: var(--accent2, #00a884); }

.cp-card-foot {
    padding: 12px 20px;
    border-top: 1px solid var(--main-border-color, #e5e7eb);
    background: var(--primary-background, #f9fafb);
}
.cp-card-foot a {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 9px;
    background: var(--accent1, #0c4a6e); color: #fff;
    border-radius: 8px; text-decoration: none;
    font-size: 13px; font-weight: 600;
    transition: opacity .15s;
}
.cp-card-foot a:hover { opacity: .88; }

.cp-empty {
    grid-column: 1/-1; text-align: center; padding: 64px 0;
}
.cp-empty i {
    font-size: 48px; color: #9ca3af; opacity: .4;
    display: block; margin-bottom: 14px;
}
.cp-empty p { color: #6b7280; font-size: 15px; }
</style>

{{-- Welcome banner with real profile photo --}}
@php
    $userId = session('userdata.id', -1);
    $userModified = session('userdata.modified', -1);
    $userName = session('userdata.name', session('userdata.username', 'Client'));
    $userInitial = strtoupper(mb_substr($userName, 0, 1));
    $profileSrc = BASE_URL . '/api/users?profileImage=' . $userId . '&v=' . (is_numeric($userModified) ? $userModified : 0);
@endphp

<div class="cp-welcome">
    {{-- Profile photo: loads via the same API used by the navbar, falls back to initial letter --}}
    <img class="cp-avatar"
         src="{{ $profileSrc }}"
         alt="{{ $userName }}"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
    <div class="cp-avatar-fallback" style="display:none;">{{ $userInitial }}</div>

    <div class="cp-welcome-text">
        <h1>{{ __('clientportal.headlines.dashboard') }}</h1>
        <p>Welcome back, {{ $userName }}! Here's an overview of your projects.</p>
    </div>
</div>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        @if(empty($projects))
            <div class="cp-grid">
                <div class="cp-empty">
                    <i class="fa fa-folder-open"></i>
                    <p>{{ __('clientportal.text.no_projects') }}</p>
                    <small style="color:#9ca3af;">{{ __('clientportal.text.no_projects_hint') }}</small>
                </div>
            </div>
        @else
            <p class="cp-section-title">
                <i class="fa fa-folder-open"></i>
                {{ __('clientportal.sections.your_projects') }}
            </p>

            <div class="cp-grid">
                @foreach($projects as $project)
                <div class="cp-card">
                    <div class="cp-card-body">

                        <div class="cp-card-head">
                            <h4 class="cp-card-name">
                                <i class="fa fa-folder"></i>{{ $project['name'] }}
                            </h4>
                        </div>

                        {{-- Progress --}}
                        <div class="cp-prog-label">
                            <span>{{ __('clientportal.labels.overall_progress') }}</span>
                            <span class="pct">{{ $project['percent'] }}%</span>
                        </div>
                        <div class="cp-progress-bar">
                            <span style="width:{{ $project['percent'] }}%;"></span>
                        </div>

                        {{-- Stats --}}
                        <div class="cp-meta">
                            <span>
                                <i class="fa fa-check-circle"></i>
                                {{ $project['progress']['done'] }} / {{ $project['progress']['total'] }}
                                {{ __('clientportal.labels.tasks_done') }}
                            </span>
                            <span>
                                <i class="fa fa-flag hi"></i>
                                {{ $project['milestoneDone'] }} / {{ $project['milestoneTotal'] }}
                                {{ __('clientportal.labels.milestones_done') }}
                            </span>
                            @if($project['nextMilestone'])
                            <span>
                                <i class="fa fa-circle-dot"></i>
                                {{ __('clientportal.labels.next') }}: {{ $project['nextMilestone']['headline'] }}
                            </span>
                            @endif
                        </div>

                    </div>
                    <div class="cp-card-foot">
                        <a href="{{ BASE_URL }}/clientportal/showProject/{{ $project['id'] }}">
                            {{ __('clientportal.buttons.view_project') }}
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection
