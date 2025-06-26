<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- Authentication ---
$routes->get('/', 'Login::index',['filter' => 'guest']);
$routes->get('login', 'Login::index',['filter' => 'guest']);
$routes->post('login/auth', 'Login::auth');
$routes->get('login/logout', 'Login::logout');

// --- Main Application ---
$routes->get('dashboard', 'Dashboard::index');

// --- Password Management ---
$routes->group('password', static function ($routes) {
    $routes->get('change', 'Password::change');
    $routes->post('checkoldpassword', 'Password::checkOldPassword');
    $routes->post('update', 'Password::update');
});

// --- Settings Module (RBAC, etc.) ---
$routes->group('setting', static function ($routes) {
    // User Management
    $routes->get('users', 'UserController::index');
    $routes->post('users/save', 'UserController::save');
    $routes->get('users/trash', 'UserController::trash');
    $routes->post('users/restore/(:num)', 'UserController::restore/$1');
    $routes->post('users/delete/(:num)', 'UserController::delete/$1');
    $routes->post('users/resetpassword', 'UserController::resetPassword');

    // Role Management (Uses RoleController)
    $routes->get('roles', 'RoleController::index');
    $routes->post('roles/save', 'RoleController::save');
    $routes->post('roles/delete/(:num)', 'RoleController::delete/$1');
    $routes->get('roles/trash', 'RoleController::trash');
    $routes->post('roles/restore/(:num)', 'RoleController::restore/$1');
    $routes->post('roles/savemenu', 'RoleController::saveRoleMenu');
    $routes->get('roles/getmenus/(:num)', 'RoleController::getRoleMenus/$1');

    // Menu Management (Uses MenuController)
    $routes->get('menu', 'MenuController::index');
    $routes->post('menu/save', 'MenuController::save');
    $routes->post('menu/updateorder', 'MenuController::updateOrder');
    $routes->get('menu/trash', 'MenuController::trash');
    $routes->post('menu/restore/(:num)', 'MenuController::restore/$1');
    $routes->post('menu/delete/(:num)', 'MenuController::delete/$1');
    $routes->post('menu/purge/(:num)', 'MenuController::purge/$1');
});


// $routes->group('kendaraan', ['filter' => 'rbac', 'namespace' => 'App\Controllers\Kendaraan'], static function ($routes) {
//     // Rute untuk DataKendaraanController
//     $routes->get('data-kendaraan', 'DataKendaraanController::index');
//     $routes->post('data-kendaraan/create', 'DataKendaraanController::create');
//     $routes->post('data-kendaraan/update/(:num)', 'DataKendaraanController::update/$1');
//     $routes->post('data-kendaraan/delete/(:num)', 'DataKendaraanController::delete/$1');
// });

$routes->group('', ['filter' => 'rbac', 'namespace' => 'App\Controllers\Kendaraan'], static function ($routes) {
    // Rute untuk DataKendaraanController
    $routes->get('kendaraan/data-kendaraan', 'DataKendaraanController::index');
    $routes->post('kendaraan/data-kendaraan/create', 'DataKendaraanController::create');
    $routes->post('kendaraan/data-kendaraan/update/(:num)', 'DataKendaraanController::update/$1');
    $routes->post('kendaraan/data-kendaraan/delete/(:num)', 'DataKendaraanController::delete/$1');
    $routes->get('kendaraan/data-kendaraan/trash', 'DataKendaraanController::trash');
    $routes->post('kendaraan/data-kendaraan/restore/(:num)', 'DataKendaraanController::restore/$1');
    $routes->post('kendaraan/data-kendaraan/purge/(:num)', 'DataKendaraanController::purge/$1');
});