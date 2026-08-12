<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$expo = $slug !== '' ? Expo::findBySlug($slug) : null;

$pageTitle = 'Thank You';
require __DIR__ . '/../../includes/public_header.php';
?>
<div class="form-card text-center">
    <div class="success-icon">
        <svg width="32" height="32" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.7 4.3a1 1 0 0 1 0 1.4l-6 6a1 1 0 0 1-1.4 0l-3-3a1 1 0 1 1 1.4-1.4L7 9.6l5.3-5.3a1 1 0 0 1 1.4 0z" fill="#2fa5df"/>
        </svg>
    </div>
    <h1 class="h4"><?= e(t('success_title')) ?></h1>
    <p class="text-muted mb-4">
        <?php if ($expo !== null): ?>
            <?= e(sprintf(t('success_with_expo'), $expo['name'])) ?>
        <?php else: ?>
            <?= e(t('success_generic')) ?>
        <?php endif; ?>
    </p>
    <a href="/assets/waterlift-solar.vcf" download class="btn btn-outline-primary btn-sm">
        <?= e(t('save_contact')) ?>
    </a>
</div>
<?php
require __DIR__ . '/../../includes/public_footer.php';
