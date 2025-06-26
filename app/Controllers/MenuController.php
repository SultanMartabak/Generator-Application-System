<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Libraries\CrudGenerator;
use App\Traits\MenuFileManipulator;
use App\Filters\Rbac;

class MenuController extends BaseController
{
    use MenuFileManipulator;

    public function index()
    {
        $menuModel = new MenuModel();

        // Ambil semua menu dan bangun struktur pohon
        $allMenus = $menuModel->orderBy('sort_order', 'ASC')->findAll();
        $menuTree = $this->buildMenuTree($allMenus);

        $data = [
            'menus' => $menuTree,
            'allMenusForDropdown' => $allMenus, // Untuk dropdown parent
            'title' => 'Menu Management',
            'sidebarMenus' => $this->sidebarMenus,
        ];
        return view('setting/menu', $data);
    }

    public function save()
    {
        $menuModel = new MenuModel();
        $id = $this->request->getPost('id');

        // --- RBAC Check for Save/Update Action ---
        $permissionUrl = 'setting/menu'; // The URL associated with this permission
        $permission = $id ? 'can_update' : 'can_create';

        if (!Rbac::userCan($permission, $permissionUrl)) {
            // Redirect back with an error message if the user doesn't have permission
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $data = [
            'id'          => $id,
            'name'        => $this->request->getPost('name'),
            'url'         => $this->request->getPost('url'),
            'icon'        => $this->request->getPost('icon'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
            'is_visible'  => $this->request->getPost('is_visible') ? 1 : 0,
        ];

        if (empty($id)) {
            // NEW MENU: Place at the end of the root list by default.
            // User will then use drag-and-drop to position it.
            $lastOrder = $menuModel->where('parent_id', null)->orWhere('parent_id', 0)->countAllResults();
            $data['sort_order'] = $lastOrder;
        }

        if ($menuModel->save($data) === false) {
            return redirect()->back()->withInput()->with('errors', $menuModel->errors());
        }

        $newMenuId = $id ?: $menuModel->getInsertID();
        $message = $id ? 'Menu berhasil diupdate.' : 'Menu berhasil ditambahkan.';

        // --- Quality of Life Improvement ---
        // Automatically grant full permissions for the new menu to the Super Admin role.
        // This ensures the Super Admin can immediately see and use the new menu.
        if (empty($id) && defined('SUPER_ADMIN_ROLE_ID')) {
            $roleMenuModel = model(\App\Models\RoleMenuModel::class);
            $roleMenuModel->insert([
                'role_id' => SUPER_ADMIN_ROLE_ID,
                'menu_id' => $newMenuId,
                'can_view' => 1,
                // Anda bisa menambahkan izin default lain di sini jika perlu, misal: 'can_create' => 1
            ]);
        }

        // --- Automatic Scaffolding ---
        // Check if it's a new menu, has a URL, and the 'generate_scaffold' checkbox is ticked
        if (empty($id) && !empty($data['url'])) {
            $generateScaffold = $this->request->getPost('generate_scaffold');
            $generateType = $this->request->getPost('generate_type') ?? 'basic'; // New field for scaffold type

            if ($generateScaffold) {
                try {
                    $generator = new CrudGenerator();
                    
                    // Pass additional options for generation
                    $options = [
                        'type' => $generateType,
                        'fields' => $this->request->getPost('fields') ?? [], // Optional fields configuration
                        'with_api' => $this->request->getPost('with_api') ?? false, // Option to generate API endpoints
                    ];
                    
                    $result = $generator->generate($data['url'], $data['name'], $options);
                    
                    // Store route suggestion in flash data for modal display
                    session()->setFlashdata('show_route_modal', true);
                    session()->setFlashdata('route_suggestion', $result['route_suggestion']);
                    session()->setFlashdata('generator_messages', $result['messages']);
                    
                    $message .= ' File CRUD berhasil dibuat.';
                } catch (\Exception $e) {
                    session()->setFlashdata('error', 'Gagal membuat file CRUD: ' . $e->getMessage());
                }
            }
        }
        return redirect()->to('/setting/menu')->with('success', $message);
    }

    public function delete($id)
    {
        $menuModel = new MenuModel();
        // --- RBAC Check for Delete Action ---
        if (!Rbac::userCan('can_delete', 'setting/menu')) {
            return redirect()->to('/setting/menu')->with('error', 'Anda tidak memiliki izin untuk menghapus menu.');
        }

        // Find the menu to get its URL before deleting
        $menu = $menuModel->find($id);

        if (!$menu) {
            return redirect()->to('/setting/menu')->with('error', 'Menu tidak ditemukan.');
        }

        // If the menu has a URL, move its associated CRUD files to trash
        if (!empty($menu['url'])) {
            try {
                $generator = new CrudGenerator();
                $generator->moveGeneratedFilesToTrash($menu['url']);
            } catch (\Exception $e) {
                // If moving files fails, stop and show an error
                return redirect()->to('/setting/menu')->with('error', 'Gagal memindahkan file CRUD ke sampah: ' . $e->getMessage());
            }
        }

        // Proceed to soft-delete the menu from the database
        if ($menuModel->delete($id)) {
            return redirect()->to('/setting/menu')->with('success', 'Menu berhasil dihapus dan file terkait telah dipindahkan ke sampah.');
        }
        
        // If delete fails (prevented by a callback), get the error from the model
        $error = $menuModel->errors()['general'] ?? 'Gagal menghapus menu dari database.';
        return redirect()->to('/setting/menu')->with('error', $error);
    }

    /**
     * Permanently deletes a soft-deleted menu and its associated files from trash.
     */
    public function purge($id)
    {
        // Security: Only Super Admins can perform this action.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/menu/trash')->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $menuModel = new MenuModel();
        $menu = $menuModel->onlyDeleted()->find($id);

        if (!$menu) {
            return redirect()->to('/setting/menu/trash')->with('error', 'Menu tidak ditemukan di dalam sampah.');
        }

        // Hapus file dari folder sampah secara permanen
        if (!empty($menu['url'])) {
            $this->deleteAssociatedFilesFromTrash($menu['url']);
        }

        // Hapus permanen dari database. Argumen kedua 'true' memaksa penghapusan permanen.
        $menuModel->delete($id, true);

        return redirect()->to('/setting/menu/trash')->with('success', 'Menu telah dihapus secara permanen.');
    }

    /**
     * Helper to delete associated files from the trash directory.
     *
     * @param string $urlPath The URL path of the menu.
     */
    private function deleteAssociatedFilesFromTrash(string $urlPath)
    {
        $trashPaths = $this->getTrashPaths($urlPath);

        foreach ($trashPaths as $type => $trashPath) {
            if ($type === 'view_dir' && is_dir($trashPath)) {
                $this->deleteDirectory($trashPath); // Recursively delete view directory
            } elseif (file_exists($trashPath)) {
                unlink($trashPath); // Delete file
            }
        }
    }

    /**
     * Displays a list of soft-deleted menus.
     * Accessible only by Super Admins.
     */
    public function trash()
    {
        // Security: Only Super Admins can access this page.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/menu')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $menuModel = new MenuModel();
        $data = [
            'menus' => $menuModel->onlyDeleted()->findAll(),
            'title' => 'Menu Dihapus',
            'sidebarMenus' => $this->sidebarMenus,
        ];

        return view('setting/menu_deleted', $data);
    }

    /**
     * Restores a soft-deleted menu.
     * Accessible only by Super Admins.
     */
    public function restore($id)
    {
        // Security: Only Super Admins can perform this action.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/menu/trash')->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $menuModel = new MenuModel();
        $menuModel->restore($id);

        return redirect()->to('/setting/menu/trash')->with('success', 'Menu berhasil dipulihkan.');
    }

    /**
     * Handles the AJAX request to update the menu order and hierarchy.
     */
    public function updateOrder()
    {
       // Security check: Ensure it's an AJAX request AND the user has permission.
        if (!$this->request->isAJAX() || !Rbac::userCan('can_update', 'setting/menu')) {
            // For AJAX requests, always return a JSON error, do not redirect.
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah urutan menu.'
            ]);
        }

        $menuStructure = $this->request->getJSON(true);

        if (empty($menuStructure)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No data received.']);
        }

        $menuModel = new MenuModel();
        $menuModel->db->transStart();

        foreach ($menuStructure as $item) {
            $menuModel->update($item['id'], [
                'parent_id'  => $item['parent_id'],
                'sort_order' => $item['sort_order'],
            ]);
        }

        $menuModel->db->transComplete();

        if ($menuModel->db->transStatus() === false) {
           return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan urutan menu.']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Urutan menu berhasil disimpan.']);
    }

    /**
     * Helper function to build a hierarchical menu tree from a flat array.
     */
    private function buildMenuTree(array $elements, $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            // Normalisasi parent_id 0 menjadi null untuk konsistensi
            $elementParentId = ($element['parent_id'] == 0) ? null : $element['parent_id'];

            if ($elementParentId === $parentId) {
                $children = $this->buildMenuTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}