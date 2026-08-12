<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

// First action of every public/admin/ file, per CLAUDE.md.
Auth::requireAuth();

$totalExpos = Stats::totalExpos();
$activeExpos = Stats::activeExpos();
$totalSubmissions = Stats::totalSubmissions();
$submissionsToday = Stats::submissionsToday();
$possibleDuplicates = Stats::possibleDuplicates();
$expoBreakdown = Stats::perExpoBreakdown();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../../includes/admin_header.php';
?>
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-qr-code"></i></div>
                <div class="stat-label">Total Expos</div>
                <div class="stat-value"><?= (int) $totalExpos ?></div>
                <div class="stat-sub"><?= (int) $activeExpos ?> active</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-label">Total Submissions</div>
                <div class="stat-value"><?= (int) $totalSubmissions ?></div>
                <div class="stat-sub">all expos, all time</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-tile h-100">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-label">Submissions Today</div>
                <div class="stat-value"><?= (int) $submissionsToday ?></div>
                <div class="stat-sub">&nbsp;</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-tile h-100 <?= $possibleDuplicates > 0 ? 'stat-tile-warning' : '' ?>">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-label">Possible Duplicates</div>
                <div class="stat-value"><?= (int) $possibleDuplicates ?></div>
                <?php if ($possibleDuplicates > 0): ?>
                    <div class="stat-sub stat-sub-warning">flagged for review</div>
                <?php else: ?>
                    <div class="stat-sub">none flagged</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Expos Overview</h2>
    <a href="/admin/expos/index.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-gear"></i> Manage Expos
    </a>
</div>

<?php if (empty($expoBreakdown)): ?>
    <div class="empty-state">
        <i class="bi bi-qr-code empty-state-icon"></i>
        <p class="mb-2">No expos yet.</p>
        <a href="/admin/expos/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Create Your First Expo
        </a>
    </div>
<?php else: ?>

    <!-- Mobile: stacked cards — a shrunk table here leaves dead space and
         wraps "View Submissions" mid-word. -->
    <div class="d-md-none">
        <?php foreach ($expoBreakdown as $row): ?>
            <div class="card overview-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <h3 class="h6 mb-0"><?= e($row['name']) ?></h3>
                        <?php if ($row['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-4 small text-muted mb-3">
                        <span class="tabular-nums">
                            <strong class="text-body"><?= (int) $row['submission_count'] ?></strong> submissions
                        </span>
                        <span class="tabular-nums">
                            <?php if ((int) $row['duplicate_count'] > 0): ?>
                                <strong class="text-warning-emphasis"><?= (int) $row['duplicate_count'] ?></strong> duplicates
                            <?php else: ?>
                                <strong class="text-body">0</strong> duplicates
                            <?php endif; ?>
                        </span>
                    </div>
                    <a href="/admin/submissions/index.php?expo_id=<?= (int) $row['id'] ?>"
                       class="btn btn-sm btn-outline-secondary w-100">View Submissions</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Desktop/tablet: full table -->
    <div class="d-none d-md-block table-responsive">
        <table class="table table-hover align-middle bg-white">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="text-end">Submissions</th>
                    <th class="text-end">Duplicates</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expoBreakdown as $row): ?>
                    <tr>
                        <td><?= e($row['name']) ?></td>
                        <td>
                            <?php if ($row['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end tabular-nums"><?= (int) $row['submission_count'] ?></td>
                        <td class="text-end tabular-nums">
                            <?php if ((int) $row['duplicate_count'] > 0): ?>
                                <span class="text-warning-emphasis fw-semibold"><?= (int) $row['duplicate_count'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('M j, Y', strtotime((string) $row['created_at']))) ?></td>
                        <td class="text-end">
                            <a href="/admin/submissions/index.php?expo_id=<?= (int) $row['id'] ?>"
                               class="btn btn-sm btn-outline-secondary">View Submissions</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/admin_footer.php'; ?>
