<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderQueryIndexes extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE orders ADD INDEX idx_orders_status (status), ADD INDEX idx_orders_created_at (created_at)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE orders DROP INDEX idx_orders_status, DROP INDEX idx_orders_created_at');
    }
}