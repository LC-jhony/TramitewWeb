<header class="dark:bg-primary-700 bg-primary-600 to-indigo-500 pb-62 text-white shadow">
          @php
        use App\Filament\Resources\Catalogs\CatalogResource;
        use Filament\Facades\Filament;
        use Filament\Livewire\DatabaseNotifications;
        use Filament\Livewire\GlobalSearch;
        use Filament\Support\Facades\FilamentView;
        use Filament\Support\Icons\Heroicon;
        use Filament\View\PanelsIconAlias;
        use Filament\View\PanelsRenderHook;
        use function Filament\Support\generate_href_html;
        use function Filament\Support\prepare_inherited_attributes;
        $navigation = filament()->getNavigation();
        $hasNavigation = filament()->hasNavigation();
        $hasTopNavigation = filament()->hasTopNavigation();
        $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
        $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
        $isRtl = __('filament-panels::layout.direction') === 'rtl';
    @endphp
<nav class="fi-topbar">
        {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_START) }}

        {{-- Sidebar Toggle Buttons --}}
        @if ($hasNavigation)
            <x-filament::icon-button
                color="gray"
                :icon="Heroicon::OutlinedBars3"
                :icon-alias="PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.open()"
                x-show="! $store.sidebar.isOpen"
                class="fi-topbar-open-sidebar-btn"
            />

            <x-filament::icon-button
                color="gray"
                :icon="Heroicon::OutlinedXMark"
                :icon-alias="PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.close()"
                x-show="$store.sidebar.isOpen"
                class="fi-topbar-close-sidebar-btn"
            />
        @endif

        <div class="fi-topbar-start">
            {{-- Logo and collapsible sidebar buttons --}}
            @if ($isSidebarCollapsibleOnDesktop)
                <x-filament::icon-button
                    color="gray"
                    :icon="$isRtl ? Heroicon::OutlinedChevronLeft : Heroicon::OutlinedChevronRight"
                    :icon-alias="
                        $isRtl
                        ? [
                            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL,
                            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON,
                        ]
                        : PanelsIconAlias::SIDEBAR_EXPAND_BUTTON
                    "
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.open()"
                    x-show="! $store.sidebar.isOpen"
                    class="fi-topbar-open-collapse-sidebar-btn"
                />
            @endif

            @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                <x-filament::icon-button
                    color="gray"
                    :icon="$isRtl ? Heroicon::OutlinedChevronRight : Heroicon::OutlinedChevronLeft"
                    :icon-alias="
                        $isRtl
                        ? [
                            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL,
                            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON,
                        ]
                        : PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON
                    "
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.close()"
                    x-show="$store.sidebar.isOpen"
                    class="fi-topbar-close-collapse-sidebar-btn"
                />
            @endif

            {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_BEFORE) }}

            @if ($homeUrl = filament()->getHomeUrl())
                <a {{ generate_href_html($homeUrl) }}>
                    <x-filament-panels::logo/>
                </a>
            @else
                <x-filament-panels::logo/>
            @endif

            {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER) }}
        </div>

        {{-- Custom Team and Catalog Navigation --}}
        <div class="lg:flex items-center gap-x-4 hidden">
            {{-- Team Switcher --}}
    

            @if($hasNavigation)
                @foreach ($navigation as $group)
                    @php
                        $groupLabel = $group->getLabel();
                        $groupExtraTopbarAttributeBag = $group->getExtraTopbarAttributeBag();
                        $isGroupActive = $group->isActive();
                        $groupIcon = '';
                        $groupUrl = '';
                        $groupBadge = '';
                        $isFirstGroup = $loop->first;
                        if($isGroupActive) {
                            // Find the active item
                            foreach ($group->getItems() as $item) {
                                if ($item->isActive()) {
                                    $groupBadge = $item->getBadge();
                                    $groupIcon = $item->getIcon();
                                    $groupLabel = $item->getLabel();
                                    $groupUrl = $item->getUrl();
                                }
                            }
                        }
                    @endphp

                    <span class="text-gray-400">/</span>

                    @if ($groupLabel)
                        <a href="{{ $groupUrl }}"
                           class="text-sm font-medium flex inline-flex items-center gap-2 text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-lg p-2">
                            @if($groupIcon)
                                <x-filament::icon
                                    :icon="$groupIcon"
                                    class="h-6 w-6 rounded-full text-gray-500"
                                />
                            @endif
                            {{ $groupLabel }}

                            @if ($groupBadge)
                                <x-filament::badge>{{ $groupBadge }}</x-filament::badge>
                            @endif
                        </a>
                        <x-filament::dropdown placement="bottom-center" teleport max-height="80dvh">
                            <x-slot name="trigger">
                                <x-filament::icon-button
                                    color="gray"
                                    :icon="Heroicon::ChevronDown"
                                    size="sm"
                                    class="text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-lg p-1"
                                />
                            </x-slot>

                            @php
                                $lists = [];
                                foreach ($group->getItems() as $item) {
                                    if ($childItems = $item->getChildItems()) {
                                        $lists[] = [
                                            $item,
                                            ...$childItems,
                                        ];
                                        $lists[] = [];
                                        continue;
                                    }
                                    if (empty($lists)) {
                                        $lists[] = [$item];
                                        continue;
                                    }
                                    $lists[count($lists) - 1][] = $item;
                                }
                                if (empty($lists[count($lists) - 1])) {
                                    array_pop($lists);
                                }
                            @endphp

                            @foreach ($lists as $list)
                                <x-filament::dropdown.list>
                                    @foreach ($list as $item)
                                        @php
                                            $isItemActive = $item->isActive();
                                            $itemBadge = $item->getBadge();
                                            $itemBadgeColor = $item->getBadgeColor();
                                            $itemBadgeTooltip = $item->getBadgeTooltip();
                                            $itemUrl = $item->getUrl();
                                            $itemIcon = $isItemActive ? ($item->getActiveIcon() ?? $item->getIcon()) : $item->getIcon();
                                            $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
                                        @endphp

                                        <x-filament::dropdown.list.item
                                            :badge="$itemBadge"
                                            :badge-color="$itemBadgeColor"
                                            :badge-tooltip="$itemBadgeTooltip"
                                            :color="$isItemActive ? 'primary' : 'gray'"
                                            :href="$itemUrl"
                                            :icon="$itemIcon"
                                            tag="a"
                                            :target="$shouldItemOpenUrlInNewTab ? '_blank' : null"
                                        >
                                            {{ $item->getLabel() }}
                                        </x-filament::dropdown.list.item>
                                    @endforeach
                                </x-filament::dropdown.list>
                            @endforeach
                        </x-filament::dropdown>
                    @endif

                @endforeach

            @endif
        </div>

        <div
            @if (filament()->hasTenancy())
                x-persist="topbar.end.panel-{{ filament()->getId() }}.tenant-{{ filament()->getTenant()?->getKey() }}"
            @else
                x-persist="topbar.end.panel-{{ filament()->getId() }}"
            @endif
            class="fi-topbar-end"
        >
            {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled())
                @livewire(GlobalSearch::class)
            @endif

            {{ FilamentView::renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    @livewire(DatabaseNotifications::class, [
                         'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                     ])
                @endif

                @if (filament()->hasUserMenu())
                    <x-filament-panels::user-menu/>
                @endif
            @endif
        </div>

        {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_END) }}
    </nav>

    <x-filament-actions::modals/>
</header>
