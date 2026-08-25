<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFieldsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'name',
            ],
            'profile_image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'username',
            ],
        ]);

        $this->db->query("UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE username IS NULL");
        $this->db->query('ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)');
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['username', 'profile_image']);
    }
}
