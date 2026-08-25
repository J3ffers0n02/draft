<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryAddressToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'delivery_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'client_email',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'delivery_address');
    }
}
