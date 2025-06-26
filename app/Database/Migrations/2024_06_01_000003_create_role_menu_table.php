<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleMenuTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'menu_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey(['role_id', 'menu_id'], true);
        $this->forge->createTable('role_menu');
    }

    public function down()
    {
        $this->forge->dropTable('role_menu');
    }
}
