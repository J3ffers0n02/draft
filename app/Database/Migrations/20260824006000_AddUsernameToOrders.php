<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'after' => 'client_name',
            ],
        ]);
        $this->db->query('ALTER TABLE orders ADD INDEX idx_orders_username (username)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE orders DROP INDEX idx_orders_username');
        $this->forge->dropColumn('orders', 'username');
    }
}
