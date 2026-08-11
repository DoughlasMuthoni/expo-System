<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
    flashSet('Invalid request.', 'error');
    redirect('/admin/expos/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$expo = $id > 0 ? Expo::find($id) : null;

if ($expo === null) {
    flashSet('Expo not found.', 'error');
    redirect('/admin/expos/index.php');
}

$targetUrl = rtrim($config['app']['base_url'], '/') . '/expo/?slug=' . urlencode($expo['slug']);
QrGenerator::generate($expo['slug'], $targetUrl);

flashSet('QR code regenerated for "' . $expo['name'] . '".');
redirect('/admin/expos/index.php');
