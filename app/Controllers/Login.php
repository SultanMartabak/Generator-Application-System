<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Login extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation.
     */
    protected $helpers = ['url', 'form', 'session'];

    public function index()
    {
        return view('v_login');
    }

    public function auth()
    {
        $session = session();
        // Validasi input
        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');
        if (!$username || !$password) {
            return redirect()->back()->with('error', 'Username dan password wajib diisi.');
        }

        // Ambil user dari database
        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            // KRUSIAL: Periksa apakah user aktif
            if ($user['is_active'] != 1) {
                $session->setFlashdata('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
                return redirect()->back()->withInput();
            }

            // Ambil role_id dari tabel user_roles
            $userRoleModel = model(\App\Models\UserRoleModel::class);
            $userRoles = $userRoleModel->where('user_id', $user['id'])->findAll();
            $roleIds = array_column($userRoles, 'role_id');

            // Untuk kesederhanaan, ambil role_id pertama. Sesuaikan jika Anda ingin menyimpan semua role_id.
            $mainRoleId = !empty($roleIds) ? $roleIds[0] : null;

            // Ambil nama role berdasarkan role_id utama
            $roleName = 'N/A'; // Nilai default jika role tidak ditemukan
            if ($mainRoleId) {
                $roleModel = model(\App\Models\RoleModel::class);
                $role = $roleModel->find($mainRoleId);
                if ($role) {
                    $roleName = $role['name'];
                }
            }

            // Set session user
            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role_id' => $mainRoleId, // Simpan role_id utama
                'role_name' => $roleName, // Simpan nama role
                'isLoggedIn' => true
            ]);
           
            // (Best Practice) Update last login timestamp
            $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

            return redirect()->to('/dashboard');
        } else {
            // Username atau password salah
            $session->setFlashdata('error', 'Username atau password salah.');
            return redirect()->back()->withInput();
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}