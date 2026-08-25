<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'invoice_number' => ['type' => 'VARCHAR', 'constraint' => 32],
            'client_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'client_phone' => ['type' => 'VARCHAR', 'constraint' => 30],
            'client_email' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'order_details' => ['type' => 'TEXT'],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Order received'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_number', false, true);
        $this->forge->addKey('client_phone');
        $this->forge->createTable('orders');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
