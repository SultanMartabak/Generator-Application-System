<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException; // Import PageNotFoundException

class Password extends BaseController
{
    private function checkAccess()
    {
        if (!session()->get('user_id')) {
            // Lebih baik melempar exception atau redirect daripada exit()
            throw new PageNotFoundException('The action you requested is not allowed.');
        }
    }

    public function change()
    {
        $data['title'] = 'Change Password';
        $this->checkAccess();
        return view('v_change_password', [
            'sidebarMenus' => $this->sidebarMenus,
        ]);
    }

    public function checkOldPassword()
    {
        $this->checkAccess();
        $userId = session()->get('user_id');
        $oldPassword = $this->request->getPost('old_password');
        $userModel = new UserModel();
        
        $user = $userModel->find($userId);
        $valid = ($user && password_verify($oldPassword, $user['password_hash']));

        return $this->response->setJSON(['valid' => $valid]);
    }

    public function update()
    {
        // Pastikan pengguna sudah login sebelum melanjutkan
        $this->checkAccess();
        $userId = session()->get('user_id');
        $userModel = new UserModel();

        // Definisikan aturan validasi
        $validationRules = [
            'old_password' => [
                'label' => 'Password Lama',
                'rules' => 'required|old_password_check[' . $userId . ']', // Menggunakan custom rule
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'old_password_check' => 'Password lama yang Anda masukkan salah.'
                ]
            ],
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[8]|differs[old_password]', // Password baru tidak boleh sama dengan password lama
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'differs' => '{field} tidak boleh sama dengan Password Lama.'
                ]
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password Baru',
                'rules' => 'required|matches[new_password]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'matches' => '{field} tidak cocok dengan Password Baru.'
                ]
            ]
        ];

        // Jalankan validasi
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Jika validasi berhasil, hash password baru dan update di database
        $hashedPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        $userModel->update($userId, ['password_hash' => $hashedPassword]);

        return redirect()->to('/password/change')->with('success', 'Password berhasil diganti.');
    }
}
