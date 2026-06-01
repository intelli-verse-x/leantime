@php
    /**
     * @todo Move this to Composer, or find a better
     *       way to add filters for all passed variables
     */
    use Leantime\Domain\Auth\Models\Roles;
    $settingsLink = $tpl->dispatchTplFilter(
        'settingsLink',
        $settingsLink,
        ['type' => $menuType]
    );
@endphp


@dispatchEvent('beforeMenu')

<div class="side-nav-inner" style="display:flex; flex-direction:column; gap:4px; margin-top: 16px;">

    @dispatchEvent('afterMenuOpen')

    @if ($allAvailableProjects
        || !session()->has("currentProject")
        || $menuType == "personal"
        || $menuType == "company")

        @foreach ($menuStructure as $key => $menuItem)

            @includeIf("menu::partials.leftnav.".$menuItem['type'], ["menuItem" => $menuItem, "module" => $module, "action" => $action])

        @endforeach



    @endif

    @dispatchEvent('beforeMenuClose')

</div>
@dispatchEvent('afterMenuClose')


@once
    @push('scripts')
        <script>
            jQuery(document).ready(function () {
                leantime.menuController.initProjectSelector();
                leantime.menuController.initLeftMenuHamburgerButton();
            });
        </script>
    @endpush
@endonce
