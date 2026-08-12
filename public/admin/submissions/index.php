<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$expoFilterId = isset($_GET['expo_id']) && $_GET['expo_id'] !== '' ? (int) $_GET['expo_id'] : null;
$duplicatesOnly = isset($_GET['duplicates_only']);
$expos = Expo::all();
$activeFilterExpo = $expoFilterId !== null ? Expo::find($expoFilterId) : null;

// An expo_id in the URL that doesn't exist just falls back to "All Expos" rather than erroring.
if ($expoFilterId !== null && $activeFilterExpo === null) {
    $expoFilterId = null;
}

$submissions = Submission::allWithExpo($expoFilterId, $duplicatesOnly);

$pageTitle = 'Submissions';
$activeNav = 'submissions';
require __DIR__ . '/../../../includes/admin_header.php';
?>
<?php $exportQs = ($expoFilterId !== null ? '&expo_id=' . $expoFilterId : '') . ($duplicatesOnly ? '&duplicates_only=1' : ''); ?>
<h1 class="h3 mb-3">Submissions</h1>
<div class="d-flex flex-column flex-md-row gap-2 justify-content-md-between align-items-md-center mb-4">
    <form method="get" class="d-flex flex-wrap align-items-center gap-2">
        <label for="expo_id" class="form-label mb-0 text-muted small">Expo</label>
        <select name="expo_id" id="expo_id" class="form-select form-select-sm" style="min-width: 10rem;" onchange="this.form.submit()">
            <option value="">All Expos</option>
            <?php foreach ($expos as $expo): ?>
                <option value="<?= (int) $expo['id'] ?>" <?= $expoFilterId === (int) $expo['id'] ? 'selected' : '' ?>>
                    <?= e($expo['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-check form-check-inline mb-0 ms-1">
            <input type="checkbox" class="form-check-input" id="duplicates_only" name="duplicates_only" value="1"
                   <?= $duplicatesOnly ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label small" for="duplicates_only">Duplicates only</label>
        </div>
    </form>
    <div class="d-flex gap-2">
        <a href="/admin/submissions/export.php?format=csv<?= $exportQs ?>" class="btn btn-sm btn-outline-secondary flex-fill flex-md-grow-0">
            <i class="bi bi-filetype-csv"></i> Export CSV
        </a>
        <a href="/admin/submissions/export.php?format=xlsx<?= $exportQs ?>" class="btn btn-sm btn-outline-secondary flex-fill flex-md-grow-0">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
    </div>
</div>

<?php if (empty($submissions)): ?>
    <div class="empty-state">
        <i class="bi bi-inbox empty-state-icon"></i>
        <p class="mb-0">
            No<?= $duplicatesOnly ? ' flagged duplicate' : '' ?> submissions<?= $activeFilterExpo !== null ? ' for ' . e($activeFilterExpo['name']) : '' ?> yet.
        </p>
    </div>
<?php else: ?>
    <table id="submissionsTable" class="table table-hover align-middle bg-white w-100">
            <thead>
                <tr>
                    <th data-priority="4">Submitted</th>
                    <th data-priority="7">Expo</th>
                    <th data-priority="1">Name</th>
                    <th data-priority="5">Phone</th>
                    <th data-priority="8">Interests</th>
                    <th data-priority="6">Follow-up</th>
                    <th data-priority="3">Flag</th>
                    <th class="text-end" data-priority="2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $row): ?>
                    <tr class="<?= $row['is_possible_duplicate'] ? 'table-warning' : '' ?>">
                        <td class="tabular-nums"><?= e(date('Y-m-d H:i', strtotime((string) $row['submitted_at']))) ?></td>
                        <td><?= e($row['expo_name']) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['phone']) ?></td>
                        <td><?= e($row['interests_summary'] ?? '') ?></td>
                        <td><?= e(followUpLabel($row['follow_up_method'])) ?></td>
                        <td>
                            <?php if ($row['is_possible_duplicate']): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Possible Duplicate</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/admin/submissions/view.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php endif; ?>

<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(function () {
    if ($('#submissionsTable').length) {
        // Responsive collapses lower-priority columns behind a "+" on narrow
        // screens instead of forcing a horizontal scroll — Name/Actions/Flag
        // (see data-priority on the headers) stay visible longest.
        $('#submissionsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 25,
            language: { search: 'Search submissions:' }
        });
    }
});
</script>

<?php require __DIR__ . '/../../../includes/admin_footer.php'; ?>
