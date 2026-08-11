<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$expo = $slug !== '' ? Expo::findBySlug($slug) : null;

$pageTitle = 'Thank You';
require __DIR__ . '/../../includes/public_header.php';
?>
<div class="card shadow-sm">
    <div class="card-body text-center p-4">
        <h1 class="h4">Thank You!</h1>
        <p class="text-muted mb-0">
            <?php if ($expo !== null): ?>
                Thanks for stopping by our <?= e($expo['name']) ?> booth. A member of the Waterlift Solar team will be in touch soon.
            <?php else: ?>
                Thanks for your interest in Waterlift Solar. A member of our team will be in touch soon.
            <?php endif; ?>
        </p>
    </div>
</div>
<?php
require __DIR__ . '/../../includes/public_footer.php';
