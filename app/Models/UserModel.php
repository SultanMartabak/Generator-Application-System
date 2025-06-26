<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array'; // or 'object'
    protected $useSoftDeletes = true; // Set to true if you want soft deletes

    // Kolom yang diizinkan untuk diisi (insert/update)
    protected $allowedFields = ['username', 'email', 'password_hash', 'name', 'is_active', 'last_login_at', 'password', 'deleted_at'];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // 'datetime', 'date', or 'int'
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Only if useSoftDeletes is true

    // Validation rules are now centralized in the model
    protected $validationRules    = [
        'id'         => 'permit_empty|is_natural_no_zero',
        'username'   => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username,id,{id}]',
        'email'      => 'required|valid_email|is_unique[users.email,id,{id}]',
        'name'       => 'required|string|min_length[3]',
        'password'   => 'permit_empty|min_length[8]', // 'permit_empty' allows it to be optional on update
        'is_active'  => 'required|in_list[0,1]',
    ];
    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username ini sudah digunakan. Silakan pilih yang lain.',
        ],
        'email' => [
            'is_unique' => 'Email ini sudah terdaftar.',
        ],
    ];

    // Callbacks for automatic actions
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    protected $afterDelete  = ['cleanupUserRoles'];

    /**
     * Callback function to automatically hash the password before saving.
     */
    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']); // Remove the plain password

        return $data;
    }

    /**
     * Callback function to clean up user roles after a user is deleted.
     */
    protected function cleanupUserRoles(array $data)
    {
        if (isset($data['id'])) {
            $userIds = $data['id']; // Can be an array of IDs for batch delete
            model(UserRoleModel::class)->whereIn('user_id', $userIds)->delete();
        }
        return $data;
    }

    /**
     * Restores a soft-deleted user by setting their deleted_at field to null.
     *
     * @param int|string|null $id The user's ID.
     * @return bool
     */
    public function restore($id = null): bool
    {
        // Use withDeleted() to include soft-deleted records in the query
        return $this->withDeleted()->update($id, ['deleted_at' => null]);
    }
}
