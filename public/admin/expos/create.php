<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$errors = [];
$expo = ['name' => '', 'slug' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_active' => 1];

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
            $slug = Expo::uniqueSlug($slugBase);

            Expo::create([
                'name'       => $expo['name'],
                'slug'       => $slug,
                'location'   => $expo['location'],
                'start_date' => $expo['start_date'],
                'end_date'   => $expo['end_date'],
                'is_active'  => $expo['is_active'],
            ]);

            $targetUrl = rtrim($config['app']['base_url'], '/') . '/expo/?slug=' . urlencode($slug);
            QrGenerator::generate($slug, $targetUrl);

            flashSet('Expo "' . $expo['name'] . '" created.');
            redirect('/admin/expos/index.php');
        }
    }
}

$pageTitle = 'New Expo';
$activeNav = 'expos';
require __DIR__ . '/../../../includes/admin_header.php';
?>
<div class="row">
    <div class="col-lg-8 col-xl-6">
        <h1 class="h3 mb-4">New Expo</h1>
        <?php
        $formAction = '/admin/expos/create.php';
        $submitLabel = 'Create Expo';
        require __DIR__ . '/../../../includes/expo_form.php';
        ?>
    </div>
</div>
<?php require __DIR__ . '/../../../includes/admin_footer.php'; ?>
