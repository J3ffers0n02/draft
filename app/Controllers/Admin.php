<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\UserModel;

class Admin extends BaseController
{
    private const STATUSES = ['Pending', 'On-hand', 'On the way', 'Delivered'];
    private const PAYMENT_STATUSES = ['Not paid', 'Downpayment', 'Full payment'];

    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('admin/login');
    }

    public function attemptLogin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $users = new UserModel();
        $user = $users->where('username', $username)->where('is_active', 1)->first();

        if ($user !== null && password_verify($password, $user['password_hash'])) {
            session()->regenerate();
            session()->set(['admin_logged_in' => true, 'admin_user_id' => $user['id']]);
            return redirect()->to('/admin')->with('message', 'Welcome back.');
        }

        $adminUsername = (string) env('ADMIN_USERNAME', 'admin');
        $adminPassword = (string) env('ADMIN_PASSWORD', 'admin123');
        if ($user === null && hash_equals($adminUsername, $username) && hash_equals($adminPassword, $password)) {
            $userId = $users->insert([
                'name' => 'Administrator',
                'username' => $adminUsername,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
            ], true);
            session()->regenerate();
            session()->set(['admin_logged_in' => true, 'admin_user_id' => $userId]);
            return redirect()->to('/admin')->with('message', 'Welcome back.');
        }

        return redirect()->back()->withInput()->with('error', 'The username or password is incorrect.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }

    public function profile()
    {
        $user = $this->currentUser();
        if ($user === null) {
            return redirect()->to('/admin/login');
        }

        return view('admin/profile', ['user' => $user]);
    }

    public function updateProfile()
    {
        $user = $this->currentUser();
        if ($user === null) {
            return redirect()->to('/admin/login');
        }

        $username = trim((string) $this->request->getPost('username'));
        $rules = ['username' => 'required|min_length[3]|max_length[80]|alpha_numeric_punct'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->friendlyValidationMessage($this->validator->getErrors()));
        }

        $users = new UserModel();
        $existingUsername = $users->where('username', $username)->where('id !=', $user['id'])->first();
        if ($existingUsername !== null) {
            return redirect()->back()->withInput()->with('error', 'That username is already in use.');
        }

        $updates = ['username' => $username];
        $image = $this->request->getFile('profile_image');
        if ($image !== null && $image->isValid() && ! $image->hasMoved()) {
            if (! in_array($image->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return redirect()->back()->withInput()->with('error', 'Profile pictures must be JPG, PNG, or WebP.');
            }
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/profiles', $newName);
            $updates['profile_image'] = $newName;
        }

        $newPassword = (string) $this->request->getPost('new_password');
        if ($newPassword !== '') {
            $currentPassword = (string) $this->request->getPost('current_password');
            if (! password_verify($currentPassword, $user['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Your current password is incorrect.');
            }
            if (strlen($newPassword) < 8 || $newPassword !== $this->request->getPost('new_password_confirmation')) {
                return redirect()->back()->withInput()->with('error', 'New passwords must match and contain at least 8 characters.');
            }
            $updates['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $users->update($user['id'], $updates);
        return redirect()->to('/admin/profile')->with('message', 'Profile updated successfully.');
    }

    public function createAccount()
    {
        if ($this->currentUser() === null) {
            return redirect()->to('/admin/login');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
            'username' => 'required|min_length[3]|max_length[80]|alpha_numeric_punct',
            'password' => 'required|min_length[8]|matches[password_confirmation]',
            'password_confirmation' => 'required',
        ];

        if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('account_error', $this->friendlyValidationMessage($this->validator->getErrors()));
        }

        $users = new UserModel();
        $username = trim((string) $this->request->getPost('username'));
        if ($users->where('username', $username)->first() !== null) {
                return redirect()->back()->withInput()->with('account_error', 'That username is already in use.');
        }

        $users->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'username' => $username,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => 'admin',
            'is_active' => 1,
        ]);

            return redirect()->to('/admin/profile')->with('account_message', 'Admin account created successfully.');
    }

    private function currentUser(): ?array
    {
        if (! session()->get('admin_logged_in') || ! session()->get('admin_user_id')) {
            return null;
        }

        return (new UserModel())->find((int) session()->get('admin_user_id'));
    }

    private function friendlyValidationMessage(array $errors): string
    {
        $messages = [];
        foreach (array_keys($errors) as $field) {
            $field = (string) $field;
            $message = match (true) {
                $field === 'name' => 'Please enter a name.',
                $field === 'username' => 'Please enter a username with at least 3 characters.',
                $field === 'password' => 'Please enter a password with at least 8 characters.',
                $field === 'password_confirmation' => 'Please make sure both passwords match.',
                $field === 'client_name' => 'Please enter the customer name.',
                $field === 'client_phone' => 'Please enter a valid phone number.',
                $field === 'delivery_address' => 'Please enter the delivery address.',
                $field === 'invoice_number' => 'Please enter an invoice number.',
                $field === 'status' => 'Please choose an order status.',
                $field === 'payment_status' => 'Please choose a payment status.',
                str_starts_with($field, 'item_name') => 'Please enter a name for every item.',
                str_starts_with($field, 'item_price') => 'Please enter a valid price for every item.',
                default => 'Please check the information and try again.',
            };
            $messages[$message] = true;
        }

        return implode(' ', array_keys($messages));
    }

    public function dashboard()
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $query = trim((string) $this->request->getGet('q'));
        $customerUsername = trim((string) $this->request->getGet('customer_username'));
        $statusFilter = trim((string) $this->request->getGet('status_filter'));
        $dateFrom = trim((string) $this->request->getGet('date_from'));
        $dateTo = trim((string) $this->request->getGet('date_to'));
        $tab = (string) $this->request->getGet('tab');
        $selectedTab = $tab === 'delivered' ? 'delivered' : 'active';
        $statusSort = (string) $this->request->getGet('status_sort');
        $statusOptions = self::STATUSES;
        $selectedStatusSort = in_array($statusSort, $statusOptions, true) ? $statusSort : '';

        $model = new OrderModel();
        if ($customerUsername !== '') {
            $db = db_connect();
            $model->where('BINARY username = ' . $db->escape($customerUsername), null, false);
        } elseif ($statusFilter === 'Delivered' || $selectedTab === 'delivered') {
            $model->where('status', 'Delivered')
                ->where('payment_status', 'Full payment')
                ->where("NOT EXISTS (
                    SELECT 1 FROM orders AS outstanding
                    WHERE BINARY outstanding.username = BINARY orders.username
                    AND (outstanding.status != 'Delivered' OR outstanding.payment_status != 'Full payment')
                )", null, false);
            $selectedTab = 'delivered';
            $selectedStatusSort = 'Delivered';
        } elseif (in_array($statusFilter, self::STATUSES, true) && $statusFilter !== 'Pending') {
            $model->where('status', $statusFilter);
            $selectedTab = 'active';
        } elseif ($statusFilter === 'Pending' || $selectedTab === 'active') {
            $model->groupStart()
                ->where('status !=', 'Delivered')
                ->orWhere('payment_status !=', 'Full payment')
                ->groupEnd();
            $selectedTab = 'active';
        }

        if ($query !== '') {
            $db = db_connect();
            $searchPattern = '%' . $db->escapeLikeString($query) . '%';
            $escapedPattern = $db->escape($searchPattern);

            $model->groupStart()
                ->where('BINARY invoice_number LIKE ' . $escapedPattern, null, false)
                ->orWhere('BINARY client_name LIKE ' . $escapedPattern, null, false)
                ->orWhere('BINARY username LIKE ' . $escapedPattern, null, false)
                ->orWhere('BINARY client_phone LIKE ' . $escapedPattern, null, false)
                ->groupEnd();
        }

        if ($selectedStatusSort !== '' && $selectedTab === 'active') {
            $model->where('status', $selectedStatusSort);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $model->where('created_at >=', $dateFrom . ' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $model->where('created_at <=', $dateTo . ' 23:59:59');
        }

        $orders = $model
            ->orderBy("FIELD(status, 'Pending', 'On-hand', 'On the way', 'Delivered')", '', false)
            ->orderBy('created_at', 'DESC')
            ->paginate(25);

        return view('admin/dashboard', [
            'orders' => $orders,
            'pager' => $model->pager,
            'query' => $query,
            'customerUsername' => $customerUsername,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedTab' => $selectedTab,
            'selectedStatusSort' => $selectedStatusSort,
            'counts' => [
                'total' => (new OrderModel())->countAllResults(),
                'pending' => (new OrderModel())->where('status', 'Pending')->countAllResults(),
                'on_hand' => (new OrderModel())->where('status', 'On-hand')->countAllResults(),
                'transit' => (new OrderModel())->where('status', 'On the way')->countAllResults(),
                'delivered' => (new OrderModel())->where('status', 'Delivered')->countAllResults(),
                'total_revenue' => (new OrderModel())->selectSum('total_amount')->first()['total_amount'] ?? 0,
                'total_sales' => (new OrderModel())->where('status', 'Delivered')->countAllResults(),
            ],
        ]);
    }

    public function clientOrders()
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $username = trim((string) $this->request->getGet('username'));
        if ($username === '') {
            return redirect()->to('/admin');
        }

        $db = db_connect();
        $orders = (new OrderModel())
            ->where('BINARY username = ' . $db->escape($username), null, false)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('admin/client_orders', [
            'orders' => $orders,
            'username' => $username,
            'client' => $orders[0] ?? null,
        ]);
    }

    public function createOrder()
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $rules = [
            'client_name' => 'required|min_length[2]',
            'username' => 'required|min_length[3]|max_length[80]|alpha_numeric_punct',
            'client_phone' => 'required|min_length[7]',
            'delivery_address' => 'required|min_length[5]|max_length[255]',
            'item_name.*' => 'required|min_length[1]',
            'item_price.*' => 'required|decimal|greater_than_equal_to[0]',
            'payment_status' => 'required|in_list[Not paid,Downpayment,Full payment]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('create_order_error', $this->friendlyValidationMessage($this->validator->getErrors()));
        }

        $itemNames = $this->request->getPost('item_name') ?? [];
        $itemPrices = $this->request->getPost('item_price') ?? [];
        $items = [];
        $total = 0;

        foreach ($itemNames as $index => $itemName) {
            $price = (float) ($itemPrices[$index] ?? 0);
            $name = trim((string) $itemName);

            if ($name === '') {
                continue;
            }

            $items[] = ['name' => $name, 'price' => number_format($price, 2, '.', '')];
            $total += $price;
        }

        if ($items === []) {
            return redirect()->back()->withInput()->with('create_order_error', 'Add at least one item to the order.');
        }

        $invoice = $this->nextInvoiceNumber();
        (new OrderModel())->insert([
            'invoice_number' => $invoice,
            'client_name' => trim((string) $this->request->getPost('client_name')),
            'username' => trim((string) $this->request->getPost('username')),
            'client_phone' => trim((string) $this->request->getPost('client_phone')),
            'client_email' => trim((string) $this->request->getPost('client_email')) ?: null,
            'delivery_address' => trim((string) $this->request->getPost('delivery_address')),
            'order_details' => implode(', ', array_column($items, 'name')),
            'items' => json_encode($items, JSON_THROW_ON_ERROR),
            'note' => trim((string) $this->request->getPost('note')) ?: null,
            'total_amount' => number_format($total, 2, '.', ''),
            'status' => 'Pending',
            'payment_status' => $this->request->getPost('payment_status'),
        ]);

        return redirect()->to('/admin')->with('create_order_message', "Invoice {$invoice} created successfully.");
    }

    private function nextInvoiceNumber(): string
    {
        $result = (new OrderModel())
            ->select('MAX(CAST(invoice_number AS UNSIGNED)) AS highest_number', false)
            ->first();
        $highestNumber = (int) ($result['highest_number'] ?? 0);

        return (string) max($highestNumber + 1, 1000001);
    }

    public function updateStatus(int $id)
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $status = (string) $this->request->getPost('status');
        if (in_array($status, self::STATUSES, true)) {
            (new OrderModel())->update($id, ['status' => $status]);
        }

        return redirect()->to('/admin');
    }

    public function updatePaymentStatus(int $id)
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $paymentStatus = (string) $this->request->getPost('payment_status');
        if (in_array($paymentStatus, self::PAYMENT_STATUSES, true)) {
            (new OrderModel())->update($id, ['payment_status' => $paymentStatus]);
        }

        return redirect()->to('/admin');
    }

    public function editOrder(int $id)
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $model = new OrderModel();
        $order = $model->find($id);
        if ($order === null) {
            return redirect()->to('/admin')->with('error', 'That order could not be found.');
        }

        $rules = [
            'invoice_number' => 'required|max_length[32]',
            'client_name' => 'required|min_length[2]',
            'username' => 'required|min_length[3]|max_length[80]|alpha_numeric_punct',
            'client_phone' => 'required|min_length[7]',
            'delivery_address' => 'required|min_length[5]|max_length[255]',
            'item_name.*' => 'required|min_length[1]',
            'item_price.*' => 'required|decimal|greater_than_equal_to[0]',
            'status' => 'required|in_list[' . implode(',', self::STATUSES) . ']',
            'payment_status' => 'required|in_list[Not paid,Downpayment,Full payment]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('edit_order_error', $this->friendlyValidationMessage($this->validator->getErrors()))
                ->with('edit_order_id', $id);
        }

        $invoiceNumber = trim((string) $this->request->getPost('invoice_number'));
        $duplicateInvoice = $model
            ->where('invoice_number', $invoiceNumber)
            ->where('id !=', $id)
            ->first();
        if ($duplicateInvoice !== null) {
            return redirect()->back()->withInput()
                ->with('edit_order_error', 'That invoice number is already in use.')
                ->with('edit_order_id', $id);
        }

        $itemNames = $this->request->getPost('item_name') ?? [];
        $itemPrices = $this->request->getPost('item_price') ?? [];
        $items = [];
        $total = 0;

        foreach ($itemNames as $index => $itemName) {
            $name = trim((string) $itemName);
            $price = (float) ($itemPrices[$index] ?? 0);

            if ($name === '') {
                continue;
            }

            $items[] = ['name' => $name, 'price' => number_format($price, 2, '.', '')];
            $total += $price;
        }

        if ($items === []) {
            return redirect()->back()->withInput()
                ->with('edit_order_error', 'Add at least one item to the order.')
                ->with('edit_order_id', $id);
        }

        $model->update($id, [
            'invoice_number' => $invoiceNumber,
            'client_name' => trim((string) $this->request->getPost('client_name')),
            'username' => trim((string) $this->request->getPost('username')),
            'client_phone' => trim((string) $this->request->getPost('client_phone')),
            'client_email' => trim((string) $this->request->getPost('client_email')) ?: null,
            'delivery_address' => trim((string) $this->request->getPost('delivery_address')),
            'order_details' => implode(', ', array_column($items, 'name')),
            'items' => json_encode($items, JSON_THROW_ON_ERROR),
            'note' => trim((string) $this->request->getPost('note')) ?: null,
            'total_amount' => number_format($total, 2, '.', ''),
            'status' => $this->request->getPost('status'),
            'payment_status' => $this->request->getPost('payment_status'),
        ]);

        return redirect()->to('/admin')
            ->with('edit_order_message', "Invoice {$invoiceNumber} updated successfully.")
            ->with('edit_order_id', $id);
    }

    public function deleteOrder(int $id)
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $model = new OrderModel();
        $order = $model->find($id);

        if ($order !== null) {
            $model->delete($id);
            return redirect()->to('/admin')->with('message', "Invoice {$order['invoice_number']} deleted.");
        }

        return redirect()->to('/admin')->with('error', 'That order could not be found.');
    }
}
