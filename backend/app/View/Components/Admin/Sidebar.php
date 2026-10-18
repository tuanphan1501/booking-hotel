<?php

namespace App\View\Components\Admin;

use App\Models\AdminUser;
use App\Navigation\AdminMenu;
use App\Navigation\MenuGroup;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Web Admin sidebar: logo and the menu entries the signed-in administrator may see.
 *
 * Usage: <x-admin.sidebar />
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Sidebar extends Component
{
    /**
     * Menu groups rendered by the view.
     *
     * @var list<MenuGroup>
     */
    public array $menuGroups;

    /**
     * @param  AdminMenu  $adminMenu  Sidebar definition.
     * @param  AuthFactory  $auth  Authentication manager, used to read the "admin" guard.
     */
    public function __construct(AdminMenu $adminMenu, AuthFactory $auth)
    {
        $administrator = $auth->guard('admin')->user();

        $this->menuGroups = $administrator instanceof AdminUser ? $adminMenu->visibleFor($administrator) : [];
    }

    /**
     * Get the view that renders the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.admin.sidebar');
    }
}
