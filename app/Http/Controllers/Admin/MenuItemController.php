<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\MenuItemService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    use PreventsManagerDelete;

    protected $menuItemService;

    public function __construct(MenuItemService $menuItemService)
    {
        $this->menuItemService = $menuItemService;
    }

    public function getData(Request $request)
    {
        $menuId = $request->route('menu') ?? $request->get('menu');
        $menuItems = $this->menuItemService->getMenuItemsByMenuId($menuId);

        return datatables()->of($menuItems)
            ->addColumn('action', function ($item) use ($menuId) {
                $editBtn = '<a href="'.route('menus.items.edit', ['menu' => $menuId, 'item' => $item->id]).'" class="btn btn-sm btn-primary">Edit</a>';
                $deleteBtn = '<form action="'.route('admin.items.destroy', ['item' => $item->id]).'" method="POST" style="display:inline-block;" class="delete-menu-item-form">
                    '.csrf_field().method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>';

                return auth()->user()->role === 'manager' ? $editBtn : $editBtn.' '.$deleteBtn;
            })
            ->make(true);
    }

    public function index()
    {
        return view('admin.menu_items.index');
    }

    public function create($menuId)
    {
        $menu = Menu::findOrFail($menuId);
        $menus = Menu::all();
        $languages = Language::where('active', 1)->get();

        return view('admin.menu_items.create', compact('menu', 'menus', 'languages'));
    }

    public function store(Request $request, $menuId)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'order_number' => 'required|integer',
            'parent_id' => 'nullable|exists:menu_items,id',
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.*' => 'nullable|string|max:255',
        ]);

        try {
            $this->menuItemService->createMenuItem($request, $menuId);

            return redirect()->route('admin.menus.items.index', ['menu' => $menuId])
                ->with('success', __('cms.menu_items.created'));
        } catch (\Exception $e) {
            return back()->with('error', __('cms.menu_items.creation_failed'));
        }
    }

    public function edit($id)
    {
        $menuItem = MenuItem::with(['menu', 'translations'])->findOrFail($id);
        $menus = Menu::with('menuItems.translations')->get();
        $languages = Language::where('active', 1)->get();

        return view('admin.menu_items.edit', compact('menuItem', 'menus', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order_number' => 'required|integer',
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.*' => 'nullable|string|max:255',
        ]);

        $this->menuItemService->updateMenuItem($request, $request->menu_id, $id);

        return redirect()->route('admin.menus.item.index')
            ->with('success', __('cms.menu_items.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $menuItem = MenuItem::findOrFail($id);
        if ($menuItem->delete()) {
            return response()->json([
                'success' => true,
                'message' => __('cms.menu_items.deleted'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error deleting menu item.',
        ]);
    }
}
