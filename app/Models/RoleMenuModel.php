<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $table            = 'role_menus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_id', 'menu_id', 'can_view', 'can_create', 'can_update', 'can_delete', 'can_reset_password'];
}