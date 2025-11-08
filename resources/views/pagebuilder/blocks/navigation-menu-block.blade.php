@php
    use App\Domains\Navigation\Models\Menu;

    $menuId = $config['menu_id'] ?? null;
    $displayStyle = $config['display_style'] ?? 'horizontal';
    $alignment = $config['alignment'] ?? 'left';
    $showIcons = $config['show_icons'] ?? true;
    $maxDepth = $config['max_depth'] ?? 3;
    $mobileBreakpoint = $config['mobile_breakpoint'] ?? 'md';
    $theme = $config['theme'] ?? 'default';

    $menu = $menuId ? Menu::with('items')->find($menuId) : null;
    $items = $menu ? $menu->getStructure() : [];

    // Alignment classes
    $alignmentClasses = [
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end',
        'justify' => 'justify-between',
    ];

    // Theme classes
    $themeClasses = [
        'default' => 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white',
        'minimal' => 'bg-transparent text-gray-700 dark:text-gray-300',
        'modern' => 'bg-gradient-to-r from-blue-500 to-purple-600 text-white',
        'classic' => 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 border-b border-gray-300 dark:border-gray-700',
    ];

    // Render menu item recursively
    $renderMenuItem = function($item, $depth = 0) use (&$renderMenuItem, $showIcons, $maxDepth, $displayStyle) {
        if ($depth >= $maxDepth || !$item['is_visible']) {
            return '';
        }

        $hasChildren = !empty($item['children']);
        $url = $item['url'] ?? '#';
        $target = $item['target'] ?? '_self';
        $icon = $showIcons && !empty($item['icon']) ? $item['icon'] : '';

        $itemClasses = $displayStyle === 'horizontal'
            ? 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors relative group'
            : 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors';

        $html = '<li class="' . ($displayStyle === 'horizontal' ? 'relative' : '') . '">';
        $html .= '<a href="' . $url . '" target="' . $target . '" class="flex items-center gap-2 ' . $itemClasses . '">';

        if ($icon) {
            $html .= '<span class="w-5 h-5 flex-shrink-0">' . $icon . '</span>';
        }

        $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';

        if ($hasChildren) {
            $html .= '<svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />';
            $html .= '</svg>';
        }

        $html .= '</a>';

        if ($hasChildren && $depth + 1 < $maxDepth) {
            $submenuClasses = $displayStyle === 'horizontal'
                ? 'absolute left-0 top-full mt-1 min-w-[200px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10'
                : 'pl-4 mt-1 space-y-1';

            $html .= '<ul class="' . $submenuClasses . '">';

            foreach ($item['children'] as $child) {
                $html .= $renderMenuItem($child, $depth + 1);
            }

            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    };
@endphp

<nav class="navigation-menu-block {{ $themeClasses[$theme] }} px-4 py-3 rounded-lg"
     data-block-id="{{ $blockId }}"
     data-block-type="{{ $blockType }}"
     data-menu-id="{{ $menuId }}">

    @if($menu && count($items) > 0)
        <ul class="{{ $displayStyle === 'horizontal' ? 'flex flex-wrap gap-2 ' . $alignmentClasses[$alignment] : 'space-y-1' }}">
            @foreach($items as $item)
                {!! $renderMenuItem($item) !!}
            @endforeach
        </ul>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            @if(!$menuId)
                <p class="text-sm">No menu selected. Please configure this block.</p>
            @else
                <p class="text-sm">Menu not found or has no visible items.</p>
            @endif
        </div>
    @endif
</nav>

<style>
    /* Mobile menu styles */
    @media (max-width: {{ $mobileBreakpoint === 'sm' ? '640px' : ($mobileBreakpoint === 'md' ? '768px' : ($mobileBreakpoint === 'lg' ? '1024px' : '1280px')) }}) {
        .navigation-menu-block ul {
            @apply flex-col space-y-1;
        }

        .navigation-menu-block li > ul {
            @apply static opacity-100 visible shadow-none border-0 mt-1 pl-4;
        }
    }

    /* Nested dropdown positioning */
    .navigation-menu-block .group:hover > ul {
        @apply opacity-100 visible;
    }

    /* Smooth transitions */
    .navigation-menu-block ul {
        transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
    }
</style>
