<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'invoice_number',
        'client_name',
        'username',
        'client_phone',
        'client_email',
        'delivery_address',
        'order_details',
        'items',
        'note',
        'total_amount',
        'status',
        'payment_status',
    ];
    protected $useTimestamps = true;
}
