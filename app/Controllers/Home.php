<?php

namespace App\Controllers;

use App\Models\OrderModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('track');
    }

    public function search()
    {
        $query = trim((string) $this->request->getGet('q'));

        if ($query === '') {
            return $this->response->setJSON(['orders' => []]);
        }

        $db = db_connect();

        $orders = (new OrderModel())
            ->where('BINARY username = ' . $db->escape($query), null, false)
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        return $this->response->setJSON(['orders' => $orders]);
    }
}
