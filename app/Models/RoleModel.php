<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description','updated_at','created_at' , 'deleted_at'];

    // Callbacks
    protected $afterDelete = ['cleanupRelations'];

    // Dates
    protected $useTimestamps = false;
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'name' => 'required|is_unique[roles.name,id,{id}]'
    ];
    protected $validationMessages   = [
        'name' => [
            'is_unique' => 'Nama role sudah ada.'
        ]
    ];
    /**
     * Callback function to clean up related data after a role is deleted.
     */
    protected function cleanupRelations(array $data)
    {
        if (isset($data['id'])) {
            $roleIds = $data['id']; // Can be an array of IDs

            // 1. Delete from role_menus
            model(RoleMenuModel::class)->whereIn('role_id', $roleIds)->delete();

            // 2. Delete from user_roles
            model(UserRoleModel::class)->whereIn('role_id', $roleIds)->delete();
        }
        return $data;
    }

    /**
     * Restores a soft-deleted role.
     */
    public function restore($id = null): bool
    {
        return $this->withDeleted()->update($id, ['deleted_at' => null]);
    }
}