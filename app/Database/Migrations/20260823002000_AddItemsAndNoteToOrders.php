<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemsAndNoteToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'items' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'order_details',
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'items',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['items', 'note']);
    }
}
