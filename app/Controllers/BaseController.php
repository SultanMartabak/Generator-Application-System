<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\MenuModel; // Import MenuModel Anda

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new properties as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['url', 'form', 'session']; // Tambahkan 'session' jika belum ada

    protected $perPage = 10;

    protected $sidebarMenus; // Properti untuk menyimpan data menu sidebar

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Hanya muat menu sidebar jika pengguna sudah login
        if (session()->get('isLoggedIn')) {
            $menuModel = new MenuModel();
            $roleBasedMenus = $menuModel->getSidebarMenus(session()->get('role_id'));

            $this->sidebarMenus = $roleBasedMenus;
        }
    }
}