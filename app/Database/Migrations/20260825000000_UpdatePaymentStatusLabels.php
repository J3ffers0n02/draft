<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePaymentStatusLabels extends Migration
{
    public function up()
    {
        $this->db->query("UPDATE orders SET payment_status = 'Full payment' WHERE payment_status = 'Paid'");
        $this->db->query("UPDATE orders SET payment_status = 'Downpayment' WHERE payment_status = 'Not paid' OR payment_status IS NULL OR payment_status = ''");

        $this->forge->modifyColumn('orders', [
            'payment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Downpayment',
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->db->query("UPDATE orders SET payment_status = 'Paid' WHERE payment_status = 'Full payment'");
        $this->db->query("UPDATE orders SET payment_status = 'Not paid' WHERE payment_status = 'Downpayment'");

        $this->forge->modifyColumn('orders', [
            'payment_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Not paid',
                'after' => 'status',
            ],
        ]);
    }
}
