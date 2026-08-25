<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/track.css') ?>" rel="stylesheet">
</head>
<body class="public-page">
    <p class="container track-brand">Tindahan</p>
    <main class="container track-shell">
        <section class="track-intro"><h1>Know where your order is.</h1></section>
        <section class="search-panel">
            <form id="order-search" class="row g-2" autocomplete="off"><div class="col-md"><label class="visually-hidden" for="search-query">Username</label><input id="search-query" class="form-control form-control-lg" name="q" placeholder="Enter your username" required></div><div class="col-md-auto"><button class="btn btn-primary btn-lg w-100" type="submit">Find my order <span aria-hidden="true">&rarr;</span></button></div></form>
            <div id="order-tabs" class="order-tabs" role="tablist" aria-label="Order categories" hidden>
                <button class="order-tab active" type="button" role="tab" aria-selected="true" data-order-tab="running">Running orders <span id="running-count">0</span></button>
                <button class="order-tab" type="button" role="tab" aria-selected="false" data-order-tab="completed">Invoices / completed orders <span id="completed-count">0</span></button>
            </div>
            <div id="search-results" class="mt-4" aria-live="polite"><p class="empty-state">Your order updates will appear here.</p></div>
        </section>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(function () {
            function escapeHtml(value) {
                return $('<div>').text(value || '').html();
            }

            function renderItems(order) {
                const items = order.items ? JSON.parse(order.items) : [];
                if (!items.length) {
                    return '<div class="result-item"><span>' + escapeHtml(order.order_details) + '</span><strong>₱' + Number(order.total_amount).toFixed(2) + '</strong></div>';
                }

                return items.map(function (item) {
                    return '<div class="result-item"><span>' + escapeHtml(item.name) + '</span><strong>₱' + Number(item.price).toFixed(2) + '</strong></div>';
                }).join('');
            }

            function renderOrders(orders) {
                if (!orders.length) {
                    return '<p class="empty-state">No orders in this section.</p>';
                }

                return orders.map(function (order) {
                    const statusClass = order.status.toLowerCase().replaceAll(' ', '-');
                    const note = order.note ? '<div class="result-note"><strong>Note:</strong> ' + escapeHtml(order.note) + '</div>' : '';
                    const paymentStatus = order.payment_status || 'Downpayment';
                    const paymentClass = paymentStatus.toLowerCase().replaceAll(' ', '-');
                    const address = order.delivery_address ? '<p class="result-address"><strong>Delivery address:</strong> ' + escapeHtml(order.delivery_address) + '</p>' : '';
                    return '<article class="result-row"><div class="result-header"><div><span class="result-label">Invoice ' + escapeHtml(order.invoice_number) + '</span><h2>Order details</h2><p class="result-meta">' + escapeHtml(order.client_name) + ' &middot; @' + escapeHtml(order.username || '') + ' &middot; ' + escapeHtml(order.client_phone) + '</p>' + address + '</div><div class="result-statuses"><div class="status-group"><span class="status-label">Order status</span><span class="status status-' + statusClass + '">' + escapeHtml(order.status) + '</span></div><div class="status-group"><span class="status-label">Payment status</span><span class="status status-' + paymentClass + '">' + escapeHtml(paymentStatus) + '</span></div></div></div><div class="result-items">' + renderItems(order) + '<div class="result-total"><span>Total price</span><strong>₱' + Number(order.total_amount).toFixed(2) + '</strong></div></div>' + note + '</article>';
                }).join('');
            }

            $('#order-search').on('submit', function (event) {
                event.preventDefault();
                const results = $('#search-results');
                results.html('<p class="empty-state">Searching...</p>');

                $.get('<?= base_url('track/search') ?>', $(this).serialize())
                    .done(function (data) {
                        if (!data.orders.length) {
                            $('#order-tabs').prop('hidden', true);
                            results.html('<p class="empty-state">No order found. Check your username and try again.</p>');
                            return;
                        }

                        const completedOrders = data.orders.filter(function (order) {
                            return order.status === 'Delivered' && (order.payment_status || 'Downpayment') === 'Full payment';
                        });
                        const runningOrders = data.orders.filter(function (order) {
                            return !completedOrders.includes(order);
                        });

                        $('#running-count').text(runningOrders.length);
                        $('#completed-count').text(completedOrders.length);
                        $('#order-tabs').prop('hidden', false);
                        $('#search-results').html('<div data-order-panel="running">' + renderOrders(runningOrders) + '</div><div data-order-panel="completed" hidden>' + renderOrders(completedOrders) + '</div>');
                    })
                    .fail(function () {
                        $('#order-tabs').prop('hidden', true);
                        results.html('<p class="empty-state">We could not reach the order service. Please try again.</p>');
                    });
            });

            $('.order-tab').on('click', function () {
                const selectedTab = $(this).data('order-tab');
                $('.order-tab').removeClass('active').attr('aria-selected', 'false');
                $(this).addClass('active').attr('aria-selected', 'true');
                $('[data-order-panel]').prop('hidden', true);
                $('[data-order-panel="' + selectedTab + '"]').prop('hidden', false);
            });

        });
    </script>
</body>
</html>
