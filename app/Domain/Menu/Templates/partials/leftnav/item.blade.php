@if(!isset($menuItem['role']) || $login::userIsAtLeast($menuItem['role'] ?? 'editor', true))

    <a href="{{ BASE_URL . $menuItem['href'] }}"
       class="nav-link @if($module == $menuItem['module'] && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))) active @endif"
       data-tippy-content="{{ strip_tags(__($menuItem['tooltip'] ?? $menuItem['title'] ?? '')) }}"
       data-tippy-placement="right"
       preload="mouseover"
       @if(isset($menuItem['attributes']))
           @foreach($menuItem['attributes'] as $key => $value)
               {{ $key }}="{{ $value }}"
           @endforeach
       @endif
    >
        @if(isset($menuItem['icon']) && $menuItem['icon'] !== '')
            <i class="{{ $menuItem['icon'] }}"></i>
        @endif
        <span>{!! __($menuItem['title']) !!}</span>
    </a>

@endif
