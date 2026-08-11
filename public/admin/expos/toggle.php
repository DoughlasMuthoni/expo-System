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

Expo::setActive($id, !$expo['is_active']);

flashSet('Expo "' . $expo['name'] . '" ' . ($expo['is_active'] ? 'deactivated' : 'activated') . '.');
redirect('/admin/expos/index.php');
