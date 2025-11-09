<?php

declare(strict_types=1);

namespace App\Domains\Navigation;

use App\Domains\Navigation\Services\MenuServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Domains\Navigation\Models\Menu createMenu(array $data)
 * @method static \App\Domains\Navigation\Models\Menu updateMenu(\App\Domains\Navigation\Models\Menu $menu, array $data)
 * @method static bool deleteMenu(\App\Domains\Navigation\Models\Menu $menu)
 * @method static \App\Domains\Navigation\Models\Menu duplicateMenu(\App\Domains\Navigation\Models\Menu $menu, string $newName)
 * @method static \App\Domains\Navigation\Models\MenuItem createMenuItem(\App\Domains\Navigation\Models\Menu $menu, array $data)
 * @method static \App\Domains\Navigation\Models\MenuItem updateMenuItem(\App\Domains\Navigation\Models\MenuItem $item, array $data)
 * @method static bool deleteMenuItem(\App\Domains\Navigation\Models\MenuItem $item)
 * @method static void reorderItems(\App\Domains\Navigation\Models\Menu $menu, array $items)
 * @method static array exportMenu(\App\Domains\Navigation\Models\Menu $menu)
 * @method static \App\Domains\Navigation\Models\Menu importMenu(array $data)
 * @method static array getAvailableLocations()
 * @method static \App\Domains\Navigation\Models\Menu|null getMenuByLocation(string $location)
 *
 * @see \App\Domains\Navigation\Services\MenuService
 */
class NavigationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MenuServiceContract::class;
    }
}
