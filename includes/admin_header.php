<?php
/**
 * Shared admin page header/nav. Include after Auth::requireAuth() has
 * already run. Expects $pageTitle (string) and $activeNav (string) to be
 * set by the including page. Lives outside web root — never linked directly.
 */
$pageTitle ??= 'Admin';
$activeNav ??= '';
$flash = flashGet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Waterlift Solar Expo System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/brand.css" rel="stylesheet">
    <link href="/assets/css/admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/admin/dashboard.php">
            <img src="/assets/img/waterlift-logo.jpeg" alt="Waterlift Solar" height="32" class="me-2">
            <span class="d-none d-sm-inline">Waterlift Expo Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"
                aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="/admin/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeNav === 'expos' ? 'active' : '' ?>" href="/admin/expos/index.php">Expos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeNav === 'submissions' ? 'active' : '' ?>" href="/admin/submissions/index.php">Submissions</a>
                </li>
            </ul>
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 mt-3 mt-lg-0 pb-2 pb-lg-0">
                <span class="text-white-50 small">Signed in as <?= e(Auth::currentUsername() ?? '') ?></span>
                <a href="/admin/logout.php" class="btn btn-outline-light btn-sm">Log Out</a>
            </div>
        </div>
    </div>
</nav>
<div class="container py-5">
    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
