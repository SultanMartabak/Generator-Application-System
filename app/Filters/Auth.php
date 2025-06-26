<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session();
        // Logika di bawah ini berpotensi menyebabkan redirect loop atau perilaku tidak diinginkan.
        // Jika pengguna sudah login, mereka seharusnya bisa mengakses halaman lain selain dashboard.
        // if ($session->get('logged_in')) {
        //     return redirect()->to('/dashboard');
        // }
    }
}