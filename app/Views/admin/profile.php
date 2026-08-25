<?php
$user = $user ?? ['name' => '', 'profile_image' => '', 'username' => ''];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/profile.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/track.css') ?>" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="navbar site-nav">
        <div class="container">
                <p class="track-brand mb-0">Tindahan</p>
            <div class="d-flex gap-3 align-items-center">
                <a class="nav-link" href="<?= base_url('admin') ?>">Dashboard</a>
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
        </div>
    </nav>

    <main class="container profile-shell">
        <div class="profile-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-end gap-3 mb-4">
            <div>
                <p class="eyebrow">Account settings</p>
                <h1>Manage profile</h1>
                <p class="text-muted mb-0">Update your account details and profile picture.</p>
            </div>
            <button class="btn btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#addAccount">Add Account</button>
        </div>

        <?php if (session('message')): ?>
            <div class="alert alert-success"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form class="profile-form" method="post" action="<?= base_url('admin/profile') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <section class="profile-section">
                <h2>Profile details</h2>
                <div class="profile-picture-preview">
                    <?php if (! empty($user['profile_image'])): ?>
                        <img src="<?= base_url('uploads/profiles/' . $user['profile_image']) ?>" alt="Profile picture">
                    <?php else: ?>
                        <span><?= esc(strtoupper(substr($user['name'], 0, 1))) ?></span>
                    <?php endif; ?>
                </div>
                <label class="form-label" for="profile-image">Profile picture</label>
                <input id="profile-image" class="form-control" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                <small class="form-text">Use a JPG, PNG, or WebP image.</small>
                <label class="form-label mt-3" for="username">Username</label>
                <input id="username" class="form-control" name="username" value="<?= esc($user['username'] ?? '') ?>" required>
            </section>

            <section class="profile-section">
                <h2>Change password</h2>
                <p class="text-muted small">Leave these fields blank if you only want to update your username or picture.</p>
                <label class="form-label" for="current-password">Current password</label>
                <input id="current-password" class="form-control" type="password" name="current_password" autocomplete="current-password">
                <label class="form-label mt-3" for="new-password">New password</label>
                <input id="new-password" class="form-control" type="password" name="new_password" minlength="8" autocomplete="new-password">
                <label class="form-label mt-3" for="new-password-confirmation">Confirm new password</label>
                <input id="new-password-confirmation" class="form-control" type="password" name="new_password_confirmation" minlength="8" autocomplete="new-password">
            </section>

            <div class="d-flex gap-2 justify-content-end">
                <a class="btn btn-light" href="<?= base_url('admin') ?>">Cancel</a>
                <button class="btn btn-primary" type="submit">Save profile</button>
            </div>
        </form>
    </main>

    <div class="modal fade" id="addAccount" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addAccountLabel">Add admin account</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="<?= base_url('admin/accounts') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <?php if (session('account_message')): ?>
                            <div class="alert alert-success"><?= esc(session('account_message')) ?></div>
                        <?php endif; ?>
                        <?php if (session('account_error')): ?>
                            <div class="alert alert-danger"><?= esc(session('account_error')) ?></div>
                        <?php endif; ?>
                        <label class="form-label" for="account-name">Name</label>
                        <input id="account-name" class="form-control" name="name" value="<?= esc(old('name')) ?>" required minlength="2" maxlength="120">
                        <label class="form-label mt-3" for="account-username">Username</label>
                        <input id="account-username" class="form-control" name="username" value="<?= esc(old('username')) ?>" required minlength="3" maxlength="80">
                        <label class="form-label mt-3" for="account-password">Password</label>
                        <input id="account-password" class="form-control" type="password" name="password" required minlength="8" autocomplete="new-password">
                        <label class="form-label mt-3" for="account-password-confirmation">Confirm password</label>
                        <input id="account-password-confirmation" class="form-control" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Create account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (session('account_message') || session('account_error')): ?>
        <script>
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addAccount')).show();
        </script>
    <?php endif; ?>
</body>
</html>
