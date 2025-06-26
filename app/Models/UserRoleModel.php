<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table      = 'user_roles'; // Pastikan ini 'user_roles' (plural)
    protected $primaryKey = 'user_id'; // Primary key gabungan, tapi CI4 butuh satu. user_id atau biarkan default.
                                      // Untuk tabel pivot, primaryKey biasanya tidak terlalu relevan untuk operasi dasar.
                                      // Jika Anda ingin menggunakan insert/update/delete by primary key,
                                      // Anda mungkin perlu menyesuaikan cara Anda memanggilnya.
                                      // Namun, untuk whereIn('user_id', $userIds) itu sudah cukup.

    protected $useAutoIncrement = false; // Tabel pivot biasanya tidak auto-increment
    protected $returnType     = 'array';
    protected $allowedFields  = ['user_id', 'role_id'];

    // Jika Anda menggunakan timestamps di tabel pivot, tambahkan ini:
    // protected $useTimestamps = true;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
}