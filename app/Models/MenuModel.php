<?php

namespace App\Models;

use App\Libraries\CrudGenerator;
use App\Traits\MenuFileManipulator;

use CodeIgniter\Model;

class MenuModel extends Model
{
    use MenuFileManipulator;

    protected $table            = 'menu';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields = [
        'name', 
        'url', 
        'icon', 
        'parent_id', 
        'sort_order', 
        'is_active', 
        'is_visible', 
        'deleted_at',
        'has_crud',           // New field to track if CRUD was generated
        'crud_type',          // New field to store the type of CRUD generated
        'crud_options'        // New field to store JSON of CRUD generation options
    ];    // ... (Callbacks, validation rules, etc. if any)

    // Callbacks
    protected $beforeDelete = ['handleAssociatedFiles', 'deleteChildren'];
    protected $afterRestore = ['restoreChildren', 'restoreFilesFromTrash'];
    
    /**
     * Mengambil menu untuk sidebar berdasarkan hak akses role,
     * mengecualikan menu default, dan menyusunnya secara hierarkis.
     */
    public function getSidebarMenus(?int $roleId = null): array
    {
        if (is_null($roleId)) {
            return []; // Jika user tidak punya role, kembalikan menu dinamis kosong
        }

        // A list of URLs that should always be visible, regardless of role_menus configuration.
        $alwaysVisibleUrls = ['/dashboard', '/password/change'];

        // 1. Fetch all active and visible menus.
        //    Perform a LEFT JOIN with role_menus to get role-specific permissions.
        //    A LEFT JOIN is used to ensure all menus are retrieved, even if they don't have an entry in role_menus.
        $builder = $this->db->table($this->table . ' AS menu');
        $builder->select('menu.id, menu.name, menu.url, menu.icon, menu.parent_id, menu.sort_order, menu.is_active, menu.is_visible, rm.can_view');
        $builder->join('role_menus AS rm', 'rm.menu_id = menu.id AND rm.role_id = ' . $this->db->escape($roleId), 'left');
        $builder->where('menu.is_active', 1);
        $builder->where('menu.is_visible', 1); // Pastikan menu memang dimaksudkan untuk ditampilkan
        $builder->where('menu.deleted_at', null); // KRUSIAL: Hanya ambil menu yang tidak di-soft-delete
        $builder->orderBy('menu.parent_id', 'ASC');
        $builder->orderBy('menu.sort_order', 'ASC');

        $allMenusWithPermissions = $builder->get()->getResultArray();

        $finalMenus = [];
        foreach ($allMenusWithPermissions as $menu) {
            $canView = false;

            // If the menu URL is in the always-visible list, force can_view to true.
            if (in_array($menu['url'], $alwaysVisibleUrls)) {
                $canView = true;
            } else {
                // Otherwise, use the can_view value from the role_menus table.
                // This will be null if no entry exists, or 0/1.
                if (isset($menu['can_view']) && $menu['can_view'] == 1) {
                    $canView = true;
                }
            }

            // Only add the menu to the final list if it's viewable.
            if ($canView) {
                // Clean up temporary columns before adding to the final list.
                unset($menu['can_view']);
                unset($menu['is_active']);
                unset($menu['is_visible']);
                $finalMenus[] = $menu;
            }
        }

        // 2. Arrange the flat array into a hierarchical tree structure.
        $tree = [];
        $lookup = [];

        foreach ($finalMenus as $item) {
            $lookup[$item['id']] = $item;
            $lookup[$item['id']]['children'] = [];
        }
        foreach ($lookup as $id => &$item) {
            if ($item['parent_id'] !== null && $item['parent_id'] != 0 && isset($lookup[$item['parent_id']])) {
                $lookup[$item['parent_id']]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }
        return $tree;
    }

    /**
     * Main callback handler for file operations before a delete action.
     * It decides whether to move files to trash (soft delete) or delete them permanently (purge).
     */
    protected function handleAssociatedFiles(array $data): array
    {
        if (empty($data['id'])) {
            return $data;
        }

        // Check if this is a permanent deletion (purge).
        if (isset($data['purge']) && $data['purge'] === true) {
            $this->permanentlyDeleteFiles($data);
        } else {
            // Otherwise, it's a soft delete, so move files to trash.
            $this->moveFilesToTrash($data);
        }
        return $data;
    }

    /**
     * Permanently deletes associated CRUD files from the trash directory.
     * This is called from the handleAssociatedFiles callback during a purge.
     */
    protected function permanentlyDeleteFiles(array $data)
    {
        if (empty($data['id'])) {
            return;
        }

        $crudGenerator = new CrudGenerator();
        foreach ($data['id'] as $id) {
            // Since this is a `beforeDelete` callback, the record still exists.
            // We use withDeleted() to find it even if it's already in the trash,
            // which is the case when purging.
            $menu = $this->withDeleted()->find($id);
            if ($menu && !empty($menu['url'])) {
                $crudGenerator->deleteGeneratedFilesPermanently($menu['url']);
            }
        }
    }

    /**
     * Callback to recursively delete child menus before deleting a parent.
     * This prevents orphaned records in the database.
     */
    protected function deleteChildren(array $data)
    {
        if (!isset($data['id'])) {
            return $data;
        }

        $menuIdsToDelete = is_array($data['id']) ? $data['id'] : [$data['id']];

        foreach ($menuIdsToDelete as $menuId) {
            $children = $this->where('parent_id', $menuId)->findAll();
            foreach ($children as $child) {
                $this->delete($child['id']); // This will trigger the callback recursively
            }
        }

        return $data;
    }

    /**
     * Callback untuk memindahkan file-file terkait ke folder sampah saat menu di-soft-delete.
     *
     * @param array $data Data yang akan dihapus.
     * @return array Data yang telah diproses.
     */
    protected function moveFilesToTrash(array $data)
    {
        if (empty($data['id'])) {
            return $data;
        }

        foreach ($data['id'] as $id) {
            $menu = $this->find($id);
            if (!$menu || empty($menu['url'])) {
                continue; // Skip if menu not found or no URL
            }

            $appPaths = $this->getAppPaths($menu['url']);
            $trashPaths = $this->getTrashPaths($menu['url']);

            foreach ($appPaths as $type => $appPath) {
                $trashPath = $trashPaths[$type];
                $trashDir = dirname($trashPath);

                // Check if the file/directory exists before attempting to move
                if (($type === 'view_dir' && is_dir($appPath)) || (file_exists($appPath) && $type !== 'view_dir')) {
                    if (!is_dir($trashDir)) {
                        mkdir($trashDir, 0755, true); // Create trash directory if it doesn't exist
                    }
                    rename($appPath, $trashPath); // Move the file/directory
                }
            }
        }
        return $data;
    }

    /**
     * Callback untuk mengembalikan file-file terkait dari folder sampah saat menu di-restore.
     *
     * @param array $data Data yang telah dipulihkan.
     * @return array Data yang telah diproses.
     */
    protected function restoreFilesFromTrash(array $data)
    {
        if (empty($data['id']) || !$data['result']) {
            return $data;
        }

        foreach ($data['id'] as $id) {
            $menu = $this->withDeleted()->find($id); // Find the restored menu
            if (!$menu || empty($menu['url'])) {
                continue;
            }

            $trashPaths = $this->getTrashPaths($menu['url']);
            $appPaths = $this->getAppPaths($menu['url']);

            foreach ($trashPaths as $type => $trashPath) {
                $appPath = $appPaths[$type];
                $appDir = dirname($appPath);

                // Check if the file/directory exists in trash before attempting to move back
                if (($type === 'view_dir' && is_dir($trashPath)) || (file_exists($trashPath) && $type !== 'view_dir')) {
                    if (!is_dir($appDir)) {
                        mkdir($appDir, 0755, true); // Create app directory if it doesn't exist
                    }
                    rename($trashPath, $appPath); // Move the file/directory back
                }
            }
        }
        return $data;
    }

    /**
     * Callback to recursively restore child menus after a parent is restored.
     */
    protected function restoreChildren(array $data)
    {
        if (!isset($data['id']) || !$data['result']) {
            return $data;
        }

        $menuIdsToRestore = $data['id']; // This will be an array of IDs

        foreach ($menuIdsToRestore as $menuId) {
            // Find children that are still soft-deleted
            $children = $this->onlyDeleted()->where('parent_id', $menuId)->findAll();
            foreach ($children as $child) {
                $this->restore($child['id']); // This will trigger the callback recursively
            }
        }

        return $data;
    }

    /**
     * Restores a soft-deleted menu by setting its deleted_at field to null.
     *
     * @param int|string|null $id The menu's ID.
     * @return bool
     */
    public function restore($id = null): bool
    {
        // Use withDeleted() to include soft-deleted records in the query
        return $this->withDeleted()->update($id, ['deleted_at' => null]);
    }
}