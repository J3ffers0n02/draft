<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentStatusToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'payment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Not paid',
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'payment_status');
    }
}
