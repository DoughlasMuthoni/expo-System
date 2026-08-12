<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

// Already logged in? Skip straight to the dashboard.
if (Auth::isLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Username and password are required.';
        } elseif (Auth::attempt($username, $password)) {
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Waterlift Solar Expo System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/brand.css" rel="stylesheet">
    <link href="/assets/css/admin.css" rel="stylesheet">
</head>
<body style="background-color: #f6f7fb;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-5 col-lg-4">
            <div class="card auth-card mt-5">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/assets/img/waterlift-logo.jpeg" alt="Waterlift Solar"
                             class="img-fluid mb-3" style="max-height: 70px;">
                        <h1 class="h4 mb-0">Admin Login</h1>
                    </div>

                    <?php if ($error !== null): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/admin/login.php" novalidate>
                        <?= Csrf::field() ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Log In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
