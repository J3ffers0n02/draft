<?php
$orders = $orders ?? [];
$username = $username ?? '';
$client = $client ?? null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders for @<?= esc($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/dashboard.css') ?>" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="navbar site-nav">
        <div class="container">
            <p class="track-brand mb-0">Tindahan</p>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Account</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= base_url('admin/profile') ?>">Manage profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('admin/logout') ?>">Sign out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container dashboard-shell client-orders-shell">
        <div class="client-orders-heading">
            <div>
                <p class="dashboard-kicker">Customer purchase history</p>
                <h1><?= esc($client['client_name'] ?? $username) ?></h1>
                <p class="dashboard-subtitle"><a href="<?= base_url('admin?' . http_build_query(['customer_username' => $username])) ?>">@<?= esc($username) ?></a> &middot; <?= count($orders) ?> <?= count($orders) === 1 ? 'order' : 'orders' ?></p>
            </div>
            <a class="btn btn-outline-secondary" href="<?= base_url('admin') ?>"><span aria-hidden="true">&larr;</span> Back to dashboard</a>
        </div>

        <section class="orders-table client-orders-table">
            <div class="section-heading">
                <h2>Order history</h2>
                <?php if ($client): ?><span><?= esc($client['client_phone']) ?></span><?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table dashboard-orders-table align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! $orders): ?>
                            <tr><td colspan="8" class="empty-state">No orders found for this client.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                            <?php $items = json_decode($order['items'] ?? '[]', true) ?: []; ?>
                            <?php $items = $items ?: [['name' => $order['order_details'], 'price' => $order['total_amount']]]; ?>
                            <?php foreach ($items as $itemIndex => $item): ?>
                                <tr class="order-row">
                                    <td>
                                        <?php if ($itemIndex === 0): ?>
                                            <strong class="order-invoice"><?= esc($order['invoice_number']) ?></strong>
                                            <small class="order-date"><?= esc(date('M j, Y', strtotime($order['created_at']))) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong class="order-client-name"><?= esc($order['client_name']) ?></strong></td>
                                    <td><strong class="order-client-name"><?= esc($item['name']) ?></strong></td>
                                    <td>1</td>
                                    <td><strong>₱<?= number_format((float) ($item['price'] ?? 0), 2) ?></strong></td>
                                    <td><?php if ($itemIndex === 0): ?><strong>₱<?= number_format((float) $order['total_amount'], 2) ?></strong><?php endif; ?></td>
                                    <td><span class="status status-<?= url_title(strtolower($order['status']), '-', true) ?>"><?= esc($order['status']) ?></span></td>
                                    <td><?php if ($itemIndex === 0): ?><?php $paymentStatus = $order['payment_status'] ?? 'Downpayment'; ?><span class="status status-<?= url_title(strtolower($paymentStatus), '-', true) ?>"><?= esc($paymentStatus) ?></span><?php endif; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
