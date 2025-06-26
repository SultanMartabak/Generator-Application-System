<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        $perPage = 10; // Jumlah user per halaman
        $search = $this->request->getGet('search');
        $page = $this->request->getGet('page') ?? 1; // Gunakan 'page' sebagai parameter umum untuk AJAX

        $builder = $userModel->select('users.id, users.username, users.email, users.name AS nama_lengkap, users.is_active, GROUP_CONCAT(roles.name SEPARATOR ", ") as roles, GROUP_CONCAT(roles.id SEPARATOR ",") as role_ids')
                               ->join('user_roles', 'user_roles.user_id = users.id', 'left')
                               ->join('roles', 'roles.id = user_roles.role_id', 'left')
                               ->groupBy('users.id');

        // Terapkan filter pencarian jika ada istilah pencarian
        if (!empty($search)) {
            $builder->like('users.username', $search)
                      ->orLike('users.email', $search)
                      ->orLike('users.name', $search)
                      ->orLike('roles.name', $search);
        }

        $users = $builder->paginate($perPage, 'default', $page);
        $pager = $userModel->pager;

        // Jika ini adalah request AJAX, kembalikan data dalam format JSON
        if ($this->request->isAJAX()) {
            $isCurrentUserSuperAdmin = (session()->get('role_id') == SUPER_ADMIN_ROLE_ID);
            $html = view('setting/_user_list_partial', [
                'users' => $users,
                'pager' => $pager,
                'isCurrentUserSuperAdmin' => $isCurrentUserSuperAdmin,
            ]);

            return $this->response->setJSON([
                'html' => $html,
                'pagination' => $pager->links()
            ]);
        }

        $data = [
            'users' => $users,
            'pager' => $pager,
            'roles' => $roleModel->findAll(), // Untuk pilihan di modal
            'title' => 'User Management',
            'sidebarMenus' => $this->sidebarMenus,
            'search' => $search
        ];
        return view('setting/users', $data);
    }

    public function save()
    {
        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();
        $id = $this->request->getPost('id');
        $roles = $this->request->getPost('roles') ?? []; // Get roles from form

        // --- SECURITY CHECKS START ---
        $loggedInUserRoleId = session()->get('role_id');

        // 1. Mencegah non-Super Admin menetapkan role Super Admin.
        // Jika role Super Admin ada di dalam data yang dikirim DAN pengguna yang login bukan Super Admin.
        if (in_array(SUPER_ADMIN_ROLE_ID, $roles) && $loggedInUserRoleId != SUPER_ADMIN_ROLE_ID) {
            return redirect()->back()->withInput()->with('error', 'Hanya Super Admin yang dapat menetapkan role Super Admin.');
        }

        // 2. Mencegah non-Super Admin mengedit data Super Admin yang sudah ada.
        // Ini hanya berlaku saat mengedit (ketika $id ada).
        if ($id) {
            $userToEditRoles = $userRoleModel->where('user_id', $id)->findColumn('role_id') ?? [];
            if (in_array(SUPER_ADMIN_ROLE_ID, $userToEditRoles) && $loggedInUserRoleId != SUPER_ADMIN_ROLE_ID) {
                return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki izin untuk mengubah data Super Admin.');
            }
        }
        // --- SECURITY CHECKS END ---

        // Tentukan apakah ini adalah aksi edit oleh user itu sendiri
        $isSelfEdit = ($id && $id == session()->get('user_id'));

        $data = [
            'id'       => $id,
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'name' => $this->request->getPost('name'), // Sesuaikan 'name'
        ];

        // Status 'is_active' hanya bisa diubah oleh admin, bukan oleh user itu sendiri
        if (!$isSelfEdit) {
            $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;
        }

        // Only add password to data if it's provided
        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        // The model's save method will handle insert/update and validation
        if ($userModel->save($data) === false) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        // Get the user ID (either the one we updated or the new one inserted)
        $userId = $id ?: $userModel->getInsertID();

        // --- Kelola Roles dalam Transaksi (hanya untuk admin, bukan self-edit) ---
        // Role hanya bisa diubah oleh admin, bukan oleh user itu sendiri
        if (!$isSelfEdit) {
            $userModel->db->transStart();

            // 1. Hapus role lama
            $userRoleModel->where('user_id', $userId)->delete();

            // 2. Sisipkan role baru jika ada
            if (!empty($roles)) {
                $roleData = array_map(fn($roleId) => ['user_id' => $userId, 'role_id' => $roleId], $roles);
                $userRoleModel->insertBatch($roleData);
            }

            $userModel->db->transComplete();
        }

        $message = $id ? 'User berhasil diupdate.' : 'User berhasil ditambahkan.';

        return redirect()->to('/setting/users')->with('success', $message);
    }

    public function delete($id)
    {
        if (session()->get('user_id') == $id) {
            return redirect()->to('/setting/users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        
        $userModel = new UserModel();
        // The afterDelete callback in UserModel will automatically handle
        // the deletion of related roles from the user_roles table.
        $userModel->delete($id); // This triggers the callback
        return redirect()->to('/setting/users')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Displays a list of soft-deleted users.
     * Accessible only by Super Admins.
     */
    public function trash()
    {
        // Security: Only Super Admins can access this page.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/users')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $userModel = new UserModel();
        $data = [
            'users' => $userModel->onlyDeleted()->findAll(),
            'title' => 'User Dihapus',
            'sidebarMenus' => $this->sidebarMenus,
        ];

        return view('setting/users_deleted', $data);
    }

    /**
     * Restores a soft-deleted user.
     * Accessible only by Super Admins.
     */
    public function restore($id)
    {
        // Security: Only Super Admins can perform this action.
        if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
            return redirect()->to('/setting/users/trash')->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $userModel = new UserModel();
        $userModel->restore($id);

        return redirect()->to('/setting/users/trash')->with('success', 'User berhasil dipulihkan.');
    }

    public function resetPassword()
    {
        // Aksi ini dilindungi oleh filter 'rbac' yang didefinisikan di app/Config/Filters.php.
        // Kita tambahkan pengecekan izin spesifik untuk keamanan berlapis.
        if (!\App\Filters\Rbac::userCan('can_reset_password', '/setting/users')) {
             return redirect()->to('/setting/users')->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');
        $userRoleModel = new UserRoleModel();

        // --- SECURITY CHECK: Mencegah non-Super Admin mereset password Super Admin ---
        $rolesOfUserToReset = $userRoleModel->where('user_id', $userId)->findColumn('role_id') ?? [];
        
        // Periksa apakah user yang akan direset passwordnya adalah Super Admin.
        if (in_array(SUPER_ADMIN_ROLE_ID, $rolesOfUserToReset)) {
            // Jika ya, pastikan yang melakukan aksi ini juga Super Admin.
            if (session()->get('role_id') != SUPER_ADMIN_ROLE_ID) {
                return redirect()->to('/setting/users')->with('error', 'Anda tidak memiliki izin untuk mereset password Super Admin.');
            }
        }

        // Mencegah user mereset password sendiri melalui fitur admin ini
        if (session()->get('user_id') == $userId) {
            return redirect()->to('/setting/users')->with('error', 'Anda tidak dapat mereset password Anda sendiri melalui menu ini. Gunakan fitur "Ganti Password".');
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/setting/users')->with('error', 'User tidak ditemukan.');
        }

        // Kita lewati validasi karena username yang menjadi password baru
        // mungkin tidak memenuhi aturan validasi (misal: min_length 8).
        // Callback `hashPassword` di model akan tetap berjalan secara otomatis.
        $result = $userModel->skipValidation(true)->update($userId, ['password' => $user['username']]);

        if ($result === false) {
            // Tambahkan logging untuk membantu debug di masa depan jika terjadi error
            log_message('error', 'Gagal mereset password untuk user ID ' . $userId . ': ' . json_encode($userModel->errors()));
            return redirect()->to('/setting/users')->with('error', 'Terjadi kesalahan saat mereset password.');
        }

        return redirect()->to('/setting/users')->with('success', 'Password untuk user ' . esc($user['username']) . ' berhasil direset menjadi username-nya.');
    }
}