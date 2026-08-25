<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateOrderStatuses extends Migration
{
    public function up()
    {
        $this->db->query("UPDATE orders SET status = 'Pending' WHERE status = 'Order received'");
        $this->db->query("ALTER TABLE orders MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Pending'");
    }

    public function down()
    {
        $this->db->query("UPDATE orders SET status = 'Order received' WHERE status = 'Pending'");
        $this->db->query("ALTER TABLE orders MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Order received'");
    }
}
