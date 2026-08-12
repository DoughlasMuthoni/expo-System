<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';
// Explicit require, not just reliance on the Composer classmap: on shared
// hosting without CLI/Composer access, a freshly-added class can go live
// before the deployed vendor/composer/autoload_classmap.php is regenerated
// to know about it, causing a fatal "Class not found" 500.
require_once __DIR__ . '/../../../includes/FlierGenerator.php';

Auth::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$expo = $id > 0 ? Expo::find($id) : null;

if ($expo === null) {
    flashSet('Expo not found.', 'error');
    redirect('/admin/expos/index.php');
}

if (!QrGenerator::exists($expo['slug'])) {
    flashSet('This expo\'s QR code has not been generated yet - regenerate it first.', 'error');
    redirect('/admin/expos/index.php');
}

$png = FlierGenerator::render($expo);

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $expo['slug'] . '-flier.png"');
header('Content-Length: ' . strlen($png));
echo $png;
exit;
