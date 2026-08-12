<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$expos = Expo::all();

/** Shared QR cell/thumbnail markup — same content in the table and the mobile card. */
$renderQr = static function (array $expo): void {
    if (QrGenerator::exists($expo['slug'])) {
        ?>
        <a href="<?= e(QrGenerator::publicPath($expo['slug'])) ?>" target="_blank" rel="noopener">
            <img src="<?= e(QrGenerator::publicPath($expo['slug'])) ?>" alt="QR for <?= e($expo['name']) ?>" width="48" height="48">
        </a>
        <?php
    } else {
        ?>
        <div class="small">
            <span class="badge bg-warning text-dark d-block mb-1">Missing</span>
            <form method="post" action="/admin/expos/regenerate_qr.php">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $expo['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Regenerate
                </button>
            </form>
        </div>
        <?php
    }
};

/** Shared action buttons — same content in the table and the mobile card. */
$renderActions = static function (array $expo): void {
    ?>
    <a href="/admin/submissions/index.php?expo_id=<?= (int) $expo['id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-inbox"></i> Submissions
    </a>
    <a href="/admin/expos/edit.php?id=<?= (int) $expo['id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <?php if (QrGenerator::exists($expo['slug'])): ?>
        <a href="/admin/expos/flier.php?id=<?= (int) $expo['id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-image"></i> Flier
        </a>
    <?php endif; ?>
    <form method="post" action="/admin/expos/toggle.php" class="d-inline">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $expo['id'] ?>">
        <button type="submit" class="btn btn-sm btn-outline-<?= $expo['is_active'] ? 'warning' : 'success' ?>">
            <i class="bi bi-<?= $expo['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i>
            <?= $expo['is_active'] ? 'Deactivate' : 'Activate' ?>
        </button>
    </form>
    <?php
};

$pageTitle = 'Expos';
$activeNav = 'expos';
require __DIR__ . '/../../../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Expos</h1>
    <a href="/admin/expos/create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Expo
    </a>
</div>

<?php if (empty($expos)): ?>
    <div class="empty-state">
        <i class="bi bi-qr-code empty-state-icon"></i>
        <p class="mb-2">No expos yet.</p>
        <a href="/admin/expos/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Create Your First Expo
        </a>
    </div>
<?php else: ?>

    <!-- Mobile: stacked cards. A table's fixed columns leave awkward dead
         space and force buttons to wrap at this width. -->
    <div class="d-md-none">
        <?php foreach ($expos as $expo): ?>
            <div class="card expo-card mb-3">
                <div class="card-body">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0"><?php $renderQr($expo); ?></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h2 class="h6 mb-1"><?= e($expo['name']) ?></h2>
                                <?php if ($expo['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted small"><code><?= e($expo['slug']) ?></code></div>
                            <?php if (!empty($expo['location'])): ?>
                                <div class="text-muted small"><?= e($expo['location']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php $renderActions($expo); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Desktop/tablet: full table -->
    <div class="d-none d-md-block table-responsive">
        <table class="table table-hover align-middle bg-white">
            <thead>
                <tr>
                    <th>QR</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th class="d-none d-lg-table-cell">Location</th>
                    <th class="d-none d-lg-table-cell">Dates</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expos as $expo): ?>
                    <tr>
                        <td><?php $renderQr($expo); ?></td>
                        <td><?= e($expo['name']) ?></td>
                        <td><code><?= e($expo['slug']) ?></code></td>
                        <td class="d-none d-lg-table-cell"><?= e($expo['location'] ?? '') ?></td>
                        <td class="d-none d-lg-table-cell">
                            <?php if ($expo['start_date'] || $expo['end_date']): ?>
                                <?= e($expo['start_date'] ?? '?') ?> &ndash; <?= e($expo['end_date'] ?? '?') ?>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($expo['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <?php $renderActions($expo); ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../../includes/admin_footer.php'; ?>
