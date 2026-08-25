<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveEmailFromUsers extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE users DROP INDEX email');
        $this->forge->dropColumn('users', 'email');
    }

    public function down()
    {
        $this->forge->addColumn('users', [
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
                'null' => true,
                'after' => 'profile_image',
            ],
        ]);
        $this->db->query('UPDATE users SET email = CONCAT(username, \'@example.com\') WHERE email IS NULL');
        $this->db->query('ALTER TABLE users MODIFY email VARCHAR(160) NOT NULL');
        $this->db->query('ALTER TABLE users ADD UNIQUE KEY email (email)');
    }
}
