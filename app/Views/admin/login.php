<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="admin-page">
    <main class="login-shell">
        <div class="login-copy">
            <h1>Welcome back.</h1>
        </div>
        <form class="login-form" method="post" action="<?= base_url('admin/login') ?>">
            <?= csrf_field() ?>
            <label for="username">Username</label>
            <input id="username" class="form-control" type="text" name="username" value="<?= old('username') ?>" autocomplete="username" required>
            <label for="password">Password</label>
            <input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required>
            <?php if (session('error')): ?>
                <div class="alert alert-danger"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <button class="btn btn-primary w-100" type="submit">Sign in <span aria-hidden="true">&rarr;</span></button>
        </form>
    </main>
</body>
</html>
