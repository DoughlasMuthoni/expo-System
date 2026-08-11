<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$existing = $id > 0 ? Expo::find($id) : null;

if ($existing === null) {
    flashSet('Expo not found.', 'error');
    redirect('/admin/expos/index.php');
}

$errors = [];
$expo = $existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $expo['name'] = trim((string) ($_POST['name'] ?? ''));
        $expo['slug'] = trim((string) ($_POST['slug'] ?? ''));
        $expo['location'] = trim((string) ($_POST['location'] ?? ''));
        $expo['start_date'] = trim((string) ($_POST['start_date'] ?? ''));
        $expo['end_date'] = trim((string) ($_POST['end_date'] ?? ''));
        $expo['is_active'] = isset($_POST['is_active']) ? 1 : 0;

        $errors = validateExpoInput($expo);

        if (empty($errors)) {
            $slugBase = $expo['slug'] !== '' ? slugify($expo['slug']) : slugify($expo['name']);
            $newSlug = Expo::uniqueSlug($slugBase, $id);
            $slugChanged = $newSlug !== $existing['slug'];

            Expo::update($id, [
                'name'       => $expo['name'],
                'slug'       => $newSlug,
                'location'   => $expo['location'],
                'start_date' => $expo['start_date'],
                'end_date'   => $expo['end_date'],
                'is_active'  => $expo['is_active'],
            ]);

            if ($slugChanged) {
                QrGenerator::delete($existing['slug']);
            }

            $targetUrl = rtrim($config['app']['base_url'], '/') . '/expo/?slug=' . urlencode($newSlug);
            QrGenerator::generate($newSlug, $targetUrl);

            flashSet('Expo "' . $expo['name'] . '" updated.');
            redirect('/admin/expos/index.php');
        }
    }
}

$pageTitle = 'Edit Expo';
$activeNav = 'expos';
require __DIR__ . '/../../../includes/admin_header.php';
?>
<div class="row">
    <div class="col-lg-8 col-xl-6">
        <h1 class="h3 mb-4">Edit Expo</h1>
        <?php
        $formAction = '/admin/expos/edit.php?id=' . $id;
        $submitLabel = 'Save Changes';
        require __DIR__ . '/../../../includes/expo_form.php';
        ?>
    </div>
</div>
<?php require __DIR__ . '/../../../includes/admin_footer.php'; ?>
