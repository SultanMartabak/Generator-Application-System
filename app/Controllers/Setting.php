<?php

namespace App\Controllers;

use App\Models\RoleModel;

use App\Models\MenuModel;

class Setting extends BaseController
{
    public function menu()
    {
        $data['title'] = 'Menu Setting';
        $menuModel = new MenuModel();
        $perPage = 10;
        $rootMenusPaged = $menuModel->getRootMenusPaged($perPage);
        $pager = $menuModel->pager;

        // Determine active menu based on current URL
        // Normalize path to match database storage convention (e.g., /setting/menu)
        $currentPathForView = '/' . ltrim(service('uri')->getPath(), '/');
        $activeMenu = $menuModel->where('url', $currentPathForView)->first();

        // Block access if menu is inactive
        // This check is largely handled by the RBAC filter now.
        // Kept for defense in depth, but RBAC should prevent access to inactive menus.
        if ($activeMenu && !$activeMenu['is_active']) {
            return redirect()->to('/dashboard')->with('error', 'Menu tidak aktif dan tidak dapat diakses.');
        }

        return view('setting/menu', [
            'rootMenusPaged' => $rootMenusPaged,
            'pager' => $pager,
            'menuModel' => $menuModel,
            'sidebarMenus' => $this->sidebarMenus, // Use BaseController's populated sidebarMenus
            'title' => $data['title'],
            'activeMenu' => $activeMenu,
        ]);
    }

    // Roles management
    public function roles()
    {
        $roleModel = new RoleModel();
        // $userModel = new UserModel(); // Tidak lagi diperlukan di sini
        $menuModel = new MenuModel();
        // $userRoleModel = model(\App\Models\UserRoleModel::class); // Tidak lagi diperlukan di sini
        $roleMenuModel = model(\App\Models\RoleMenuModel::class);

        $roles = $roleModel->findAll();
        $menus = $menuModel->findAll(); // Ambil semua menu

        return view('setting/roles', [
            'roles' => $roles,
            'menus' => $menus,
            'roleMenuModel' => $roleMenuModel,
            'title' => 'Role Management',
            'sidebarMenus' => $this->sidebarMenus, // Use BaseController's populated sidebarMenus
        ]);
    }

    public function saveRole()
    {
        $roleModel = new RoleModel();
        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $description = $this->request->getPost('description');

        $data = [
            'name' => $name,
            'description' => $description,
        ];

        if ($id) {
            $roleModel->update($id, $data);
            return redirect()->to('/setting/roles')->with('success', 'Role berhasil diupdate');
        } else {
            $roleModel->insert($data);
            return redirect()->to('/setting/roles')->with('success', 'Role berhasil ditambahkan');
        }
    }

    public function deleteRole($id)
    {
        $roleModel = new RoleModel();
        $userRoleModel = model(\App\Models\UserRoleModel::class);
        $roleMenuModel = model(\App\Models\RoleMenuModel::class);

        // Hapus juga relasi di user_role dan role_menu
        $userRoleModel->where('role_id', $id)->delete();
        $roleMenuModel->where('role_id', $id)->delete();

        $roleModel->delete($id);
        return redirect()->to('/setting/roles')->with('success', 'Role berhasil dihapus');
    }

    public function saveRoleMenu()
    {
        $roleMenuModel = model(\App\Models\RoleMenuModel::class);
        $roleId = $this->request->getPost('role_id_for_menu');
        $menuIds = $this->request->getPost('menu_ids') ?? [];

        // Hapus menu lama untuk role tersebut
        $roleMenuModel->where('role_id', $roleId)->delete();

        // Tambah menu baru
        if (!empty($menuIds)) {
            foreach ($menuIds as $menuId) {
                $roleMenuModel->insert(['role_id' => $roleId, 'menu_id' => $menuId]);
            }
        }
        return redirect()->to('/setting/roles')->with('success', 'Role menus berhasil diupdate.');
    }

    public function getRoleMenus($roleId)
    {
        $roleMenuModel = model(\App\Models\RoleMenuModel::class);
        $menus = $roleMenuModel->where('role_id', $roleId)->findColumn('menu_id');
        return $this->response->setJSON($menus ?? []);
    }

    public function saveMenu()
    {
        $menuModel = new MenuModel();
        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $url  = $this->request->getPost('url');
        $icon = $this->request->getPost('icon');
        $parent_id = $this->request->getPost('parent_id') ?: null;
        $is_active = $this->request->getPost('is_active') ? 1 : 0;

        $data = [
            'name' => $name,
            'url'  => $url,
            'icon' => $icon,
            'parent_id' => $parent_id,
            'is_active' => $is_active,
        ];

        if ($id) {
            $menuModel->update($id, $data);
            return redirect()->to('/setting/menu')->with('success', 'Menu berhasil diupdate');
        } else {
            $menuModel->insert($data);
            return redirect()->to('/setting/menu')->with('success', 'Menu berhasil ditambahkan');
        }
    }

    // Placeholder for deleteMenu, as it's defined in Routes.php but missing here.
    // This was likely causing a 404 if RBAC allowed access.
    public function deleteMenu($id)
    {
        $menuModel = new MenuModel();
        // Tambahkan validasi atau pemeriksaan sebelum menghapus jika perlu
        $menuModel->delete($id);
        // Anda mungkin juga ingin menghapus entri terkait di role_menu
        return redirect()->to('/setting/menu')->with('success', 'Menu berhasil dihapus.');
    }
}
