<?php
$counts = $counts ?? ['total' => 0, 'pending' => 0, 'on_hand' => 0, 'transit' => 0, 'delivered' => 0];
$orders = $orders ?? [];
$pager = $pager ?? null;
$query = $query ?? '';
$customerUsername = $customerUsername ?? '';
$selectedTab = $selectedTab ?? 'active';
$selectedStatusSort = $selectedStatusSort ?? '';
$statusFilter = $statusFilter ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$today = date('Y-m-d');
$filterParams = ['q' => $query, 'customer_username' => $customerUsername, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status_filter' => $statusFilter];
$ordersByClient = [];
foreach ($orders as $order) {
    $clientKey = ($order['username'] ?? '') !== '' ? $order['username'] : $order['client_name'] . '|' . $order['client_phone'];
    $ordersByClient[$clientKey][] = $order;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/invoice.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/dashboard.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/track.css') ?>" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="navbar site-nav">
        <div class="container">
            <p class="track-brand mb-0">Tindahan</p>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Account
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= base_url('admin/profile') ?>">Manage profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('admin/logout') ?>">Sign out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container dashboard-shell">
        <div class="dashboard-titlebar">
            <div>
                <h1><?= $customerUsername !== '' ? 'Purchase history' : 'Preorders' ?></h1>
                <p class="dashboard-subtitle"><?= $customerUsername !== '' ? 'All orders for @' . esc($customerUsername) : ($pager?->getTotal() ?? 0) . ' of ' . $counts['total'] . ' preorders' ?></p>
            </div>
            <button class="btn btn-primary dashboard-create" data-bs-toggle="modal" data-bs-target="#newOrder">
                <span aria-hidden="true">+</span> New preorder
            </button>
        </div>

        <?php if (session('message')): ?>
            <div class="alert alert-success"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="dashboard-controls">
            <div class="period-pills" aria-label="Date ranges">
                <a href="<?= base_url('admin?' . http_build_query(array_merge($filterParams, ['date_from' => $today, 'date_to' => $today]))) ?>" class="period-pill <?= $dateFrom === $today && $dateTo === $today ? 'active' : '' ?>">Today</a>
                <a href="<?= base_url('admin?' . http_build_query(array_merge($filterParams, ['date_from' => date('Y-m-d', strtotime('-6 days')), 'date_to' => $today]))) ?>" class="period-pill <?= $dateFrom === date('Y-m-d', strtotime('-6 days')) && $dateTo === $today ? 'active' : '' ?>">This week</a>
                <a href="<?= base_url('admin?' . http_build_query(array_merge($filterParams, ['date_from' => date('Y-m-01'), 'date_to' => $today]))) ?>" class="period-pill <?= $dateFrom === date('Y-m-01') && $dateTo === $today ? 'active' : '' ?>">This month</a>
                <a href="<?= base_url('admin?' . http_build_query(array_merge($filterParams, ['date_from' => date('Y-01-01'), 'date_to' => $today]))) ?>" class="period-pill <?= $dateFrom === date('Y-01-01') && $dateTo === $today ? 'active' : '' ?>">This year</a>
            </div>
            <form class="date-range-form" method="get" action="<?= base_url('admin') ?>">
                <input type="hidden" name="q" value="<?= esc($query) ?>">
                <input type="hidden" name="customer_username" value="<?= esc($customerUsername) ?>">
                <input type="hidden" name="status_filter" value="<?= esc($statusFilter) ?>">
                <label><span>From</span><input type="date" name="date_from" value="<?= esc($dateFrom) ?>"></label>
                <span class="date-separator">to</span>
                <label><span>To</span><input type="date" name="date_to" value="<?= esc($dateTo) ?>"></label>
                <button class="btn btn-dark" type="submit">Apply</button>
            </form>
        </div>

        <div class="stats-grid">
            <div><span>Total preorders</span><strong><?= $counts['total'] ?></strong></div>
            <div><span>Pending</span><strong><?= $counts['pending'] ?></strong></div>
            <div><span>Ready</span><strong><?= $counts['on_hand'] ?></strong></div>
            <div><span>Completed</span><strong><?= $counts['delivered'] ?></strong></div>
            <div><span>Total revenue</span><strong>₱<?= number_format((float) $counts['total_revenue'], 2) ?></strong></div>
            <div><span>Total sales</span><strong><?= $counts['total_sales'] ?></strong></div>
        </div>

        <?php if ($customerUsername !== ''): ?>
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4 dashboard-actions">
                <a class="btn btn-outline-secondary" href="<?= base_url('admin?tab=active') ?>">
                    <span aria-hidden="true">&larr;</span> Back to recent orders
                </a>
            </div>
        <?php endif; ?>

        <section class="orders-table">
            <div class="section-heading">
                <h2><?= $customerUsername !== '' ? 'Purchase history for @' . esc($customerUsername) : 'Recent orders' ?></h2>
                <div class="d-flex align-items-center gap-2">
                    <span><?= $pager?->getTotal() ?? 0 ?> records</span>
                </div>
            </div>

            <nav class="dashboard-tabs mb-4" aria-label="Order statuses">
                <?php foreach (['' => 'All', 'Pending' => 'Pending', 'On-hand' => 'Ready', 'Delivered' => 'Completed'] as $filterValue => $filterLabel): ?>
                    <a class="dashboard-tab <?= $statusFilter === $filterValue ? 'active' : '' ?>" href="<?= base_url('admin?' . http_build_query(array_merge($filterParams, ['status_filter' => $filterValue]))) ?>"><?= $filterLabel ?></a>
                <?php endforeach; ?>
            </nav>

            <form class="dashboard-search row g-2 mb-4" method="get" action="<?= base_url('admin') ?>">
                <input type="hidden" name="tab" value="<?= esc($selectedTab) ?>">
                <input type="hidden" name="customer_username" value="<?= esc($customerUsername) ?>">
                <input type="hidden" name="date_from" value="<?= esc($dateFrom) ?>">
                <input type="hidden" name="date_to" value="<?= esc($dateTo) ?>">
                <input type="hidden" name="status_filter" value="<?= esc($statusFilter) ?>">
                <label class="visually-hidden" for="order-search">Search orders</label>
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">⌕</span>
                        <input id="order-search" class="form-control" type="search" name="q" value="<?= esc($query) ?>" placeholder="Search by client, phone, or invoice number">
                    </div>
                </div>
                <?php if ($selectedTab === 'active'): ?>
                    <div class="col-12 col-md-4 col-lg-auto">
                        <label class="visually-hidden" for="status-sort">Filter by status</label>
                        <select id="status-sort" class="form-select h-100" name="status_sort" onchange="this.form.submit()">
                            <option value="" <?= $selectedStatusSort === '' ? 'selected' : '' ?>>All active status</option>
                            <option value="Pending" <?= $selectedStatusSort === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="On-hand" <?= $selectedStatusSort === 'On-hand' ? 'selected' : '' ?>>On-hand</option>
                            <option value="On the way" <?= $selectedStatusSort === 'On the way' ? 'selected' : '' ?>>On the way</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-primary w-100 h-100" type="submit">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table dashboard-orders-table align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Cost</th>
                            <th>Marked-up Price</th>
                            <th>Downpayment</th>
                            <th>Balance</th>
                            <th>Expected</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <?php if (! $ordersByClient): ?>
                        <tbody>
                            <tr>
                                <td colspan="8" class="empty-state">No orders found.</td>
                            </tr>
                        </tbody>
                    <?php endif; ?>

                    <?php foreach ($ordersByClient as $clientOrders): ?>
                        <?php $client = $clientOrders[0]; ?>
                        <tbody class="client-order-group">
                            <tr class="client-group-heading">
                                <td colspan="8">
                                    <div class="client-group-title">
                                        <div>
                                            <span class="client-group-label">Customer orders</span>
                                            <strong><?= esc($client['client_name']) ?></strong>
                                            <?php if (($client['username'] ?? '') !== ''): ?>
                                                <a href="<?= base_url('admin?' . http_build_query(['customer_username' => $client['username']])) ?>">@<?= esc($client['username']) ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <span><?= count($clientOrders) ?> <?= count($clientOrders) === 1 ? 'order' : 'orders' ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php foreach ($clientOrders as $order): ?>
                            <?php
                                $items = json_decode($order['items'] ?? '[]', true) ?: [];
                                $firstItem = $items[0] ?? ['name' => $order['order_details'] ?? 'No item', 'price' => (float) ($order['total_amount'] ?? 0)];
                                $qty = max(1, (int) ($order['quantity'] ?? $order['qty'] ?? 1));
                                $cost = (float) ($firstItem['price'] ?? ($order['total_amount'] ?? 0));
                                $markedUpPrice = (float) ($order['total_amount'] ?? $cost);
                                $paymentStatus = $order['payment_status'] ?? 'Downpayment';
                                $downpayment = $paymentStatus === 'Full payment' ? $markedUpPrice : ($paymentStatus === 'Not paid' ? 0 : max(0, $markedUpPrice * 0.1));
                                $balance = max(0, $markedUpPrice - $downpayment);
                                $expectedDate = ! empty($order['expected_date']) ? date('M j, Y', strtotime($order['expected_date'])) : date('M j, Y', strtotime($order['created_at']));
                            ?>
                            <tr class="order-row" data-search="<?= esc($order['invoice_number'] . '|' . $order['client_name'] . '|' . ($order['username'] ?? '') . '|' . $order['client_phone'], 'attr') ?>" data-status="<?= esc($order['status'], 'attr') ?>">
                                <td>
                                    <strong class="order-client-name"><?= esc($order['client_name']) ?></strong>
                                    <?php if (($order['username'] ?? '') !== ''): ?>
                                        <small><a href="<?= base_url('admin?' . http_build_query(['customer_username' => $order['username']])) ?>">@<?= esc($order['username']) ?></a></small>
                                    <?php endif; ?>
                                    <small><?= esc($order['client_phone']) ?></small>
                                </td>
                                <td>
                                    <strong class="order-product-name"><?= esc($firstItem['name']) ?></strong>
                                    <?php if (! empty($order['order_details']) && $order['order_details'] !== $firstItem['name']): ?>
                                        <small><?= esc($order['order_details']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $qty ?></td>
                                <td>₱<?= number_format($cost, 2) ?></td>
                                <td>₱<?= number_format($markedUpPrice, 2) ?></td>
                                <td>₱<?= number_format($downpayment, 2) ?></td>
                                <td>₱<?= number_format($balance, 2) ?></td>
                                <td><?= esc($expectedDate) ?></td>
                                <td>
                                    <span class="status status-<?= url_title(strtolower($order['status']), '-', true) ?>">
                                        <?= esc($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <button class="btn btn-sm btn-order-edit" type="button" data-bs-toggle="modal" data-bs-target="#editOrder<?= $order['id'] ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-order-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteOrder<?= $order['id'] ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <?php $items = json_decode($order['items'] ?? '[]', true) ?: []; ?>
                            <?php $editItems = $items ?: [['name' => $order['order_details'], 'price' => $order['total_amount']]]; ?>
                            <tr class="order-modal-row">
                                <td colspan="8">
                                    <div class="modal fade" id="viewOrder<?= $order['id'] ?>" tabindex="-1" aria-labelledby="viewOrderLabel<?= $order['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <div>
                                                <p class="eyebrow mb-1">Order details</p>
                                                <h2 class="modal-title" id="viewOrderLabel<?= $order['id'] ?>">
                                                    Invoice <?= esc($order['invoice_number']) ?>
                                                </h2>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h3 class="h6">Items</h3>
                                            <div class="invoice-items">
                                                <?php if ($items): ?>
                                                    <?php foreach ($items as $item): ?>
                                                        <div class="invoice-item-row">
                                                            <span><?= esc($item['name']) ?></span>
                                                            <strong>₱<?= number_format((float) $item['price'], 2) ?></strong>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="invoice-item-row">
                                                        <span><?= esc($order['order_details']) ?></span>
                                                        <strong>₱<?= number_format((float) $order['total_amount'], 2) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="invoice-total d-flex justify-content-between mt-3">
                                                <span>Total price</span>
                                                <strong>₱<?= number_format((float) $order['total_amount'], 2) ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <span>Payment status</span>
                                                <strong><?= esc($order['payment_status'] ?? 'Downpayment') ?></strong>
                                            </div>
                                            <?php if (! empty($order['note'])): ?>
                                                <div class="order-note mt-4"><strong>Note</strong><p><?= esc($order['note']) ?></p></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="order-modal-row">
                                <td colspan="8">
                                    <div class="modal fade" id="editOrder<?= $order['id'] ?>" tabindex="-1" aria-labelledby="editOrderLabel<?= $order['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <div>
                                                        <p class="eyebrow mb-1">Edit order</p>
                                                        <h2 class="modal-title" id="editOrderLabel<?= $order['id'] ?>">Invoice <?= esc($order['invoice_number']) ?></h2>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="<?= base_url('admin/orders/' . $order['id'] . '/edit') ?>">
                                                    <?= csrf_field() ?>
                                                    <div class="modal-body">
                                                        <?php if ((int) session('edit_order_id') === (int) $order['id'] && session('edit_order_message')): ?>
                                                            <div class="alert alert-success"><?= esc(session('edit_order_message')) ?></div>
                                                        <?php endif; ?>
                                                        <?php if ((int) session('edit_order_id') === (int) $order['id'] && session('edit_order_error')): ?>
                                                            <div class="alert alert-danger"><?= esc(session('edit_order_error')) ?></div>
                                                        <?php endif; ?>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-invoice-number-<?= $order['id'] ?>">Invoice number</label>
                                                                <input id="edit-invoice-number-<?= $order['id'] ?>" class="form-control" name="invoice_number" value="<?= esc($order['invoice_number']) ?>" maxlength="32" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-status-<?= $order['id'] ?>">Order status</label>
                                                                <select id="edit-status-<?= $order['id'] ?>" class="form-select" name="status" required>
                                                                    <?php foreach (['Pending', 'On-hand', 'On the way', 'Delivered'] as $status): ?>
                                                                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-client-name-<?= $order['id'] ?>">Client name</label>
                                                                <input id="edit-client-name-<?= $order['id'] ?>" class="form-control" name="client_name" value="<?= esc($order['client_name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-username-<?= $order['id'] ?>">Username</label>
                                                                <input id="edit-username-<?= $order['id'] ?>" class="form-control" name="username" value="<?= esc($order['username'] ?? '') ?>" minlength="3" maxlength="80" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-client-phone-<?= $order['id'] ?>">Phone number</label>
                                                                <input id="edit-client-phone-<?= $order['id'] ?>" class="form-control" name="client_phone" value="<?= esc($order['client_phone']) ?>" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label" for="edit-client-email-<?= $order['id'] ?>">Email <span class="text-muted">(optional)</span></label>
                                                                <input id="edit-client-email-<?= $order['id'] ?>" class="form-control" type="email" name="client_email" value="<?= esc($order['client_email'] ?? '') ?>">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label" for="edit-delivery-address-<?= $order['id'] ?>">Delivery address</label>
                                                                <textarea id="edit-delivery-address-<?= $order['id'] ?>" class="form-control" name="delivery_address" rows="2" required maxlength="255"><?= esc($order['delivery_address'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <label class="form-label mb-0">Items</label>
                                                                    <button class="btn btn-sm btn-outline-success edit-add-item" data-list="edit-item-list-<?= $order['id'] ?>" type="button">+ Add item</button>
                                                                </div>
                                                                <div id="edit-item-list-<?= $order['id'] ?>">
                                                                    <?php foreach ($editItems as $item): ?>
                                                                        <div class="item-entry row g-2 mb-2">
                                                                            <div class="col"><input class="form-control" name="item_name[]" value="<?= esc($item['name']) ?>" placeholder="Item name" required></div>
                                                                            <div class="col-auto"><div class="input-group"><span class="input-group-text">₱</span><input class="form-control item-price" name="item_price[]" type="number" min="0" step="0.01" value="<?= esc($item['price']) ?>" required></div></div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label" for="edit-note-<?= $order['id'] ?>">Note</label>
                                                                <textarea id="edit-note-<?= $order['id'] ?>" class="form-control" name="note" rows="2"><?= esc($order['note'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label" for="edit-payment-status-<?= $order['id'] ?>">Payment status</label>
                                                                <select id="edit-payment-status-<?= $order['id'] ?>" class="form-select" name="payment_status" required>
                                                                    <option value="Not paid" <?= ($order['payment_status'] ?? 'Downpayment') === 'Not paid' ? 'selected' : '' ?>>Not paid</option>
                                                                    <option value="Downpayment" <?= ($order['payment_status'] ?? 'Downpayment') === 'Downpayment' ? 'selected' : '' ?>>Downpayment</option>
                                                                    <option value="Full payment" <?= ($order['payment_status'] ?? '') === 'Full payment' ? 'selected' : '' ?>>Full payment</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-primary" type="submit">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="order-modal-row">
                                <td colspan="8">
                                    <div class="modal fade" id="deleteOrder<?= $order['id'] ?>" tabindex="-1" aria-labelledby="deleteOrderLabel<?= $order['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h2 class="modal-title" id="deleteOrderLabel<?= $order['id'] ?>">Delete order?</h2>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-0">This will permanently delete invoice <strong><?= esc($order['invoice_number']) ?></strong>.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="post" action="<?= base_url('admin/orders/' . $order['id'] . '/delete') ?>">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-danger" type="submit">Delete order</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    <?php endforeach; ?>
                    <tbody>
                        <tr id="no-search-results" class="d-none">
                            <td colspan="7" class="empty-state">No matching orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-pagination">
                <?= $pager?->links() ?>
            </div>
        </section>
    </main>

    <div class="modal fade" id="newOrder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Create an order</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="post" action="<?= base_url('admin/orders') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <?php if (session('create_order_message')): ?>
                            <div class="alert alert-success"><?= esc(session('create_order_message')) ?></div>
                        <?php endif; ?>
                        <?php if (session('create_order_error')): ?>
                            <div class="alert alert-danger"><?= esc(session('create_order_error')) ?></div>
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="client-name">Client name</label>
                                <input id="client-name" class="form-control" name="client_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="username">Username</label>
                                <input id="username" class="form-control" name="username" minlength="3" maxlength="80" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="client-phone">Phone number</label>
                                <input id="client-phone" class="form-control" name="client_phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="payment-status">Payment status</label>
                                <select id="payment-status" class="form-select" name="payment_status" required>
                                    <option value="Not paid">Not paid</option>
                                    <option value="Downpayment" selected>Downpayment</option>
                                    <option value="Full payment">Full payment</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="client-email">
                                    Email <span class="text-muted">(optional)</span>
                                </label>
                                <input id="client-email" class="form-control" type="email" name="client_email">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="delivery-address">Delivery address</label>
                                <textarea id="delivery-address" class="form-control" name="delivery_address" rows="2" maxlength="255" required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Items</label>
                                    <button class="btn btn-sm btn-outline-success" id="add-item" type="button">+ Add item</button>
                                </div>
                                <div id="item-list">
                                    <div class="item-entry row g-2 mb-2">
                                        <div class="col"><input class="form-control" name="item_name[]" placeholder="Item name" required></div>
                                        <div class="col-auto"><div class="input-group"><span class="input-group-text">₱</span><input class="form-control item-price" name="item_price[]" type="number" min="0" step="0.01" placeholder="0.00" required></div></div>
                                        <div class="col-auto"><button class="btn btn-outline-danger remove-item" type="button" aria-label="Remove item" title="Remove item">&times;</button></div>
                                    </div>
                                </div>
                                <div class="invoice-total d-flex justify-content-between"><span>Total price</span><strong>₱<span id="total-preview">0.00</span></strong></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="order-note">Note</label>
                                <textarea id="order-note" class="form-control" name="note" rows="2" placeholder="Optional delivery or customer note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Create invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.order-modal-row .modal').forEach(modal => {
            document.body.appendChild(modal);
        });
        document.querySelectorAll('.order-modal-row').forEach(row => row.remove());

        document.querySelectorAll('.dashboard-orders-table .dropdown-toggle').forEach(button => {
            bootstrap.Dropdown.getOrCreateInstance(button, {
                popperConfig: {
                    strategy: 'fixed',
                    modifiers: [
                        { name: 'preventOverflow', options: { boundary: 'viewport' } },
                    ],
                },
            });
        });

        const itemList = document.getElementById('item-list');
        const totalPreview = document.getElementById('total-preview');

        function updateTotal() {
            const total = [...document.querySelectorAll('#item-list .item-price')]
                .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
            totalPreview.textContent = total.toFixed(2);
        }

        function updateRemoveButtons() {
            const entries = itemList.querySelectorAll('.item-entry');
            entries.forEach(entry => {
                entry.querySelector('.remove-item').disabled = entries.length === 1;
            });
        }

        document.getElementById('add-item').addEventListener('click', () => {
            const entry = itemList.querySelector('.item-entry').cloneNode(true);
            entry.querySelectorAll('input').forEach(input => input.value = '');
            itemList.appendChild(entry);
            entry.querySelector('.item-price').addEventListener('input', updateTotal);
            updateRemoveButtons();
        });

        document.querySelectorAll('#item-list .item-price').forEach(input => input.addEventListener('input', updateTotal));

        itemList.addEventListener('click', event => {
            const removeButton = event.target.closest('.remove-item');
            if (!removeButton) {
                return;
            }

            removeButton.closest('.item-entry').remove();
            updateTotal();
            updateRemoveButtons();
        });

        updateRemoveButtons();

        document.querySelectorAll('.edit-add-item').forEach(button => {
            button.addEventListener('click', () => {
                const list = document.getElementById(button.dataset.list);
                const entry = list.querySelector('.item-entry').cloneNode(true);
                entry.querySelectorAll('input').forEach(input => input.value = '');
                list.appendChild(entry);
            });
        });

        <?php if (session('create_order_message') || session('create_order_error')): ?>
            bootstrap.Modal.getOrCreateInstance(document.getElementById('newOrder')).show();
        <?php endif; ?>
        <?php if (session('edit_order_id') && (session('edit_order_message') || session('edit_order_error'))): ?>
            const editedOrderModal = document.getElementById('editOrder<?= (int) session('edit_order_id') ?>');
            if (editedOrderModal) {
                bootstrap.Modal.getOrCreateInstance(editedOrderModal).show();
            }
        <?php endif; ?>
    </script>
</body>
</html>
