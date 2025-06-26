<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use App\Models\MenuModel; // Tambahkan import ini
use CodeIgniter\HTTP\IncomingRequest; // Tambahkan import ini
use CodeIgniter\HTTP\RequestInterface; // Import RequestInterface
use CodeIgniter\HTTP\ResponseInterface; // Import ResponseInterface
use CodeIgniter\Services; // Import Services for response

class Rbac implements FilterInterface
{
    private static $userRoles = null;
    private static $permissions = []; // Cache untuk izin yang sudah dicek

    public function before(RequestInterface $request, $arguments = null)
    {
         // Jika pengguna tidak login, filter ini tidak melakukan apa-apa.
        // Tugas autentikasi sudah ditangani oleh filter 'auth'.
        if (!session()->get('isLoggedIn')) {
            return; // Biarkan filter 'auth' yang menangani redirect ke login
        }

        // Hanya lakukan pengecekan 'can_view' untuk request GET.
        // Aksi POST, PUT, DELETE akan divalidasi di dalam method controller masing-masing.
        if ($request->getMethod() === 'get') {
            // Dapatkan path URL saat ini, tanpa leading/trailing slash.
            $currentUrl = trim($request->getUri()->getPath(), '/');

            // Cek apakah pengguna memiliki izin 'can_view' untuk URL ini.
            // Pastikan $request adalah instance dari IncomingRequest untuk menggunakan metode isAJAX().
            /** @var IncomingRequest $request */
            if (!self::userCan('can_view', $currentUrl)) {
                // Simpan pesan error di flashdata
                session()->setFlashdata('access_denied_error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.');

                // Untuk request AJAX, kembalikan JSON error
                if ($request->isAJAX()) {
                    return service('response')->setStatusCode(403)->setJSON([
                        'status'  => 'error',
                        'message' => 'Anda tidak memiliki izin untuk mengakses sumber daya ini.'
                    ]);
                }

                // Untuk request non-AJAX (akses langsung dari URL):
                service('response')->setStatusCode(403); // Set status HTTP ke 403 Forbidden

                // Ambil data menu sidebar untuk ditampilkan di layout
                $menuModel = new MenuModel();
                $sidebarMenus = $menuModel->getSidebarMenus(session()->get('role_id'));

                $data = [
                    'title' => 'Akses Ditolak', // Judul halaman
                    'sidebarMenus' => $sidebarMenus, // Data menu sidebar
                ];

                // Render view 'access_denied.php' yang meng-extend 'main_layout.php'
                service('response')->setBody(view('errors/html/access_denied', $data));

                // Kembalikan objek response untuk menghentikan pemrosesan lebih lanjut
                return service('response');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosongkan cache setelah request selesai
        self::$userRoles = null;
        self::$permissions = [];
    }

    public static function userCan(string $permission, string $menuUrl): bool
    {
        $userId = session()->get('user_id');
         // Normalize the input menuUrl to handle both '/path' and 'path'
        $normalizedMenuUrl = trim($menuUrl, '/');
        if (!$userId) {
            return false;
        }

        // Langkah 1: Ambil role user, HANYA SEKALI per request
        if (self::$userRoles === null) {
            $userRoleModel = new \App\Models\UserRoleModel();
            self::$userRoles = $userRoleModel->where('user_id', $userId)->findAll();
        }

        $roleIds = array_column(self::$userRoles, 'role_id');

        // Super Admin (ID 1) selalu punya akses
        // Use loose comparison (==) because DB might return role ID as a string.
        if (defined('SUPER_ADMIN_ROLE_ID') && in_array(SUPER_ADMIN_ROLE_ID, $roleIds)) {
            return true;
        }

        // Langkah 2: Cek izin dari cache internal
        $cacheKey = implode(',', $roleIds) . ':' . $menuUrl;
        if (!isset(self::$permissions[$cacheKey])) {
            // Jika tidak ada di cache, query ke DB untuk mendapatkan semua izin untuk URL ini
            // This handles cases where a user has multiple roles with different permissions for the same menu.
            // It merges the permissions, granting access if ANY of the roles have the permission.
            $db = db_connect();
            $allPerms = $db->table('role_menus')
                        ->join('menu', 'menu.id = role_menus.menu_id')
                        ->whereIn('role_menus.role_id', !empty($roleIds) ? $roleIds : [0])
                        ->groupStart()
                            ->where('menu.url', $normalizedMenuUrl) // Check without leading slash
                            ->orWhere('menu.url', '/' . $normalizedMenuUrl) // Check with leading slash
                        ->groupEnd()
                        ->select('role_menus.can_view, role_menus.can_create, role_menus.can_update, role_menus.can_delete, role_menus.can_reset_password')
                        ->get()->getResultArray();

            $mergedPerms = [
                'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_reset_password' => 0
            ];

            foreach ($allPerms as $p) {
                $mergedPerms['can_view'] = $mergedPerms['can_view'] || ($p['can_view'] ?? 0);
                $mergedPerms['can_create'] = $mergedPerms['can_create'] || ($p['can_create'] ?? 0);
                $mergedPerms['can_update'] = $mergedPerms['can_update'] || ($p['can_update'] ?? 0);
                $mergedPerms['can_delete'] = $mergedPerms['can_delete'] || ($p['can_delete'] ?? 0);
                $mergedPerms['can_reset_password'] = $mergedPerms['can_reset_password'] || ($p['can_reset_password'] ?? 0);
            }

            self::$permissions[$cacheKey] = $mergedPerms; // Simpan ke cache
        }

        // Langkah 3: Kembalikan hasil dari cache
        return !empty(self::$permissions[$cacheKey][$permission]) && self::$permissions[$cacheKey][$permission] == 1;
    }
}