<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\MenuModel;
use App\Models\RoleMenuModel;
use App\Filters\Rbac; // Import Rbac

class RoleController extends BaseController
{
    public function index()
    {
        $roleModel = new RoleModel();
        $menuModel = new MenuModel(); // Digunakan untuk mengambil semua menu

        $data = [
            'roles' => $roleModel->findAll(),
            // Ambil semua menu untuk modal "Kelola Menu".
            // Kecualikan menu 'Dashboard' dan 'Change Password' karena tidak memerlukan
            // pengaturan hak akses granular (biasanya tersedia untuk semua user yang login).
            'menus' => $menuModel->whereNotIn('url', [
                                        '/dashboard',
                                        '/password/change'
                                    ])
                                  ->orderBy('parent_id', 'ASC')
                                  ->orderBy('sort_order', 'ASC')
                                  ->findAll(),
            'title' => 'Role Management',
            'sidebarMenus' => $this->sidebarMenus,
        ];
        return view('setting/roles', $data);
    }

    public function save()
    {
        $roleModel = new RoleModel();
        $id = $this->request->getPost('id');

        // --- RBAC Check for Save/Update Action ---
        $permission = $id ? 'can_update' : 'can_create';
        if (!Rbac::userCan($permission, '/setting/roles')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        // --- Security Check: Prevent non-Super Admins from editing the Super Admin role ---
        // A Super Admin should be able to edit their own role's description, but no one else can.
        if ($id == SUPER_ADMIN_ROLE_ID && session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah role Super Admin.');
        }


        $data = [
            'id'          => $id,
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];

        if ($roleModel->save($data) === false) {
            return redirect()->back()->withInput()->with('errors', $roleModel->errors());
        }

        $message = $id ? 'Role berhasil diupdate.' : 'Role berhasil ditambahkan.';
        return redirect()->to('/setting/roles')->with('success', $message);
    }

    public function delete($id)
    {
        // --- RBAC Check for Delete Action ---
        if (!Rbac::userCan('can_delete', '/setting/roles')) {
            return redirect()->to('/setting/roles')->with('error', 'Anda tidak memiliki izin untuk menghapus role.');
        }

        // --- Security Check: Prevent deleting the Super Admin role ---
        if ($id == SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/roles')->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        $roleModel = new RoleModel();
        $roleModel->delete($id);

        return redirect()->to('/setting/roles')->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Displays a list of soft-deleted roles.
     * Accessible only by Super Admins.
     */
    public function trash()
    {
        // Security: Only Super Admins can access this page.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/roles')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $roleModel = new RoleModel();
        $data = [
            'roles' => $roleModel->onlyDeleted()->findAll(),
            'title' => 'Role Dihapus',
            'sidebarMenus' => $this->sidebarMenus,
        ];

        return view('setting/roles_deleted', $data);
    }

    /**
     * Restores a soft-deleted role.
     * Accessible only by Super Admins.
     */
    public function restore($id)
    {
        // Security: Only Super Admins can perform this action.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/roles/trash')->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $roleModel = new RoleModel();
        $roleModel->restore($id);

        return redirect()->to('/setting/roles/trash')->with('success', 'Role berhasil dipulihkan.');
    }

    public function getRoleMenus($roleId)
    {
        // --- RBAC Check: Only allow if user can update roles ---
        if (!Rbac::userCan('can_update', '/setting/roles')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        $roleMenuModel = new RoleMenuModel();
        $permissions = $roleMenuModel->where('role_id', $roleId)->findAll();
        return $this->response->setJSON($permissions);
    }

    // Save permissions from the "Manage Menu" modal
    public function saveRoleMenu()
    {
        // --- RBAC Check for Update Action ---
        if (!Rbac::userCan('can_update', '/setting/roles')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah hak akses menu.');
        }

        $roleId = $this->request->getPost('role_id_for_menu');
        $menus = $this->request->getPost('menus') ?? [];
        $roleMenuModel = new RoleMenuModel();

        // --- Security Check: Prevent non-Super Admins from editing Super Admin permissions ---
        if ($roleId == SUPER_ADMIN_ROLE_ID && session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/roles')->with('error', 'Anda tidak memiliki izin untuk mengubah hak akses Super Admin.');
        }

        $roleMenuModel->db->transStart();

        // Delete old permissions for this role
        $roleMenuModel->where('role_id', $roleId)->delete();

        // Insert new permissions
        if (!empty($menus)) {
            foreach ($menus as $menuId => $permissions) {
                $roleMenuModel->insert([
                    'role_id'            => $roleId,
                    'menu_id'            => $menuId,
                    'can_view'           => !empty($permissions['can_view']) ? 1 : 0,
                    'can_create'         => !empty($permissions['can_create']) ? 1 : 0,
                    'can_update'         => !empty($permissions['can_update']) ? 1 : 0,
                    'can_delete'         => !empty($permissions['can_delete']) ? 1 : 0,
                    'can_reset_password' => !empty($permissions['can_reset_password']) ? 1 : 0,
                ]);
            }
        }

        $roleMenuModel->db->transComplete();

        return redirect()->to('/setting/roles')->with('success', 'Hak akses menu untuk role berhasil diperbarui.');
    }
}