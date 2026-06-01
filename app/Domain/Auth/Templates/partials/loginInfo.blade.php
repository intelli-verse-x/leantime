@dispatchEvent('beforeUserinfoMenuOpen')

@php
    // Client portal users (commenters) get a slimmed-down account page that only
    // exposes personal data + theme; everyone else uses the full /users/editOwn.
    $editOwnBase = session('userdata.role') === \Leantime\Domain\Auth\Models\Roles::$commenter
        ? BASE_URL.'/clientportal/editOwn'
        : BASE_URL.'/users/editOwn';
@endphp

<div class="userinfo">
    @dispatchEvent('afterUserinfoMenuOpen')
    @if(session()->exists("companysettings.logoPath") && session("companysettings.logoPath") !== false && session("companysettings.logoPath") !== '')
        <a href='{{ $editOwnBase }}/' preload="mouseover" class="dropdown-toggle profileHandler includeLogo" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
            <img src="{{ session("companysettings.logoPath") }}" class="logo tw-pl-1" />
        </a>
    @else
        <a href='{{ $editOwnBase }}/' preload="mouseover" class="dropdown-toggle profileHandler" data-toggle="dropdown">
            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] ?? -1 }}&v={{ format($user['modified'] ?? -1)->timestamp() }}" class="profilePicture"/>
        </a>
    @endif
    <ul class="dropdown-menu">
        @dispatchEvent('afterUserinfoDropdownMenuOpen')
        <li>
            <a href='{{ $editOwnBase }}/' preload="mouseover">
                {!! __("menu.my_profile") !!}
            </a>
        </li>
        @dispatchEvent('afterMyProfile')
        @php
            $themeCore = app(\Leantime\Core\UI\Theme::class);
            $currentColorMode = $themeCore->getColorMode();
        @endphp
        <li>
            <a href="javascript:void(0);" onclick="toggleNavbarTheme()" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 15px;">
                <span>{!! __("menu.theme") !!}</span>
                <span class="theme-switcher-toggle" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-sun" id="theme-icon-light" style="color: #f1c40f; font-size: 14px; {{ $currentColorMode === 'light' ? '' : 'display: none;' }}"></i>
                    <i class="fa-solid fa-moon" id="theme-icon-dark" style="color: #3498db; font-size: 14px; {{ $currentColorMode === 'dark' ? '' : 'display: none;' }}"></i>
                    <span id="theme-text" style="font-weight: 600;">{{ $currentColorMode === 'dark' ? 'Dark' : 'Light' }}</span>
                </span>
            </a>
        </li>
        @dispatchEvent('afterTheme')
        @if(session('userdata.role') !== \Leantime\Domain\Auth\Models\Roles::$commenter)
            <li>
                <a href='{{ $editOwnBase }}#settings' preload="mouseover">
                    {!! __("menu.settings") !!}
                </a>
            </li>
            @dispatchEvent('afterSettings')
        @endif

<li class="border">
<a href='{{ BASE_URL }}/auth/logout'>
   {!! __("menu.sign_out") !!}
</a>
</li>
@dispatchEvent('beforeUserinfoDropdownMenuClose')
</ul>
@dispatchEvent('beforeUserinfoMenuClose')
</div>
@dispatchEvent('afterUserinfoMenuClose')

<script>
function toggleNavbarTheme() {
    var themeUrl = jQuery("#themeStyleSheet").attr("href");
    if (!themeUrl) return;

    var isDark = themeUrl.indexOf("dark.css") !== -1;
    var newMode = isDark ? "light" : "dark";

    // Update data-theme attribute on <html> so [data-theme="dark"] selectors
    // in light.css do not fire when switching to light mode on the current page.
    document.documentElement.setAttribute('data-theme', newMode);

    // Call the core Leantime theme toggle logic
    leantime.snippets.toggleTheme(newMode);

    // Update UI elements instantly inside the dropdown
    if (newMode === 'light') {
        jQuery('#theme-icon-light').show();
        jQuery('#theme-icon-dark').hide();
        jQuery('#theme-text').text('Light');
    } else {
        jQuery('#theme-icon-light').hide();
        jQuery('#theme-icon-dark').show();
        jQuery('#theme-text').text('Dark');
    }

    // Persist to the database in the background via AJAX PATCH
    jQuery.ajax({
        type: 'PATCH',
        url: leantime.appUrl + '/users/patchUserSettings',
        data: {
            colorMode: newMode
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).done(function(response) {
        console.log("Theme persisted: " + newMode);
    }).fail(function(xhr) {
        console.error("Failed to persist theme preference: ", xhr);
    });
}
</script>

