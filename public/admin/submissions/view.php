<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$submission = $id > 0 ? Submission::find($id) : null;

if ($submission === null) {
    flashSet('Submission not found.', 'error');
    redirect('/admin/submissions/index.php');
}

$interests = Submission::interestsFor($id);
$siblings = Submission::siblingsByPhone($id, $submission['phone'], (int) $submission['expo_id']);

$pageTitle = 'Submission #' . $id;
$activeNav = 'submissions';
require __DIR__ . '/../../../includes/admin_header.php';
?>
<div class="row">
    <div class="col-lg-10 col-xl-8">
        <div class="mb-3">
            <a href="/admin/submissions/index.php?expo_id=<?= (int) $submission['expo_id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Submissions
            </a>
        </div>

<?php if (!empty($siblings)): ?>
    <div class="alert alert-warning">
        <p class="mb-2">
            <i class="bi bi-exclamation-triangle"></i>
            <?php if ($submission['is_possible_duplicate']): ?>
                This phone number already had a submission for this expo before this one — possible duplicate.
            <?php else: ?>
                This phone number has a later submission for this expo, flagged as a possible duplicate of this one.
            <?php endif; ?>
            It hasn't been blocked; review both and decide.
        </p>
        <ul class="mb-0 ps-3">
            <?php foreach ($siblings as $sibling): ?>
                <li>
                    <a href="/admin/submissions/view.php?id=<?= (int) $sibling['id'] ?>">
                        <?= e($sibling['full_name']) ?> &mdash;
                        <?= e(date('M j, Y g:i A', strtotime((string) $sibling['submitted_at']))) ?>
                    </a>
                    <?php if ($sibling['is_possible_duplicate']): ?>
                        <span class="badge bg-warning text-dark">Possible Duplicate</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h4 mb-1"><?= e($submission['full_name']) ?></h1>
                <div class="text-muted">
                    <?= e($submission['expo_name']) ?> &middot;
                    <?= e(date('F j, Y \a\t g:i A', strtotime((string) $submission['submitted_at']))) ?>
                </div>
            </div>
        </div>

        <dl class="row gy-2 mb-0">
            <dt class="col-sm-3"><i class="bi bi-telephone text-muted me-1"></i> Phone</dt>
            <dd class="col-sm-9"><?= e($submission['phone']) ?></dd>

            <dt class="col-sm-3"><i class="bi bi-geo-alt text-muted me-1"></i> Project Location</dt>
            <dd class="col-sm-9"><?= e($submission['project_location']) ?></dd>

            <dt class="col-sm-3"><i class="bi bi-heart text-muted me-1"></i> Interests</dt>
            <dd class="col-sm-9">
                <?php if (empty($interests)): ?>
                    <span class="text-muted">&mdash;</span>
                <?php else: ?>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($interests as $interest): ?>
                            <li>
                                <?= e($interest['name']) ?>
                                <?php if (!empty($interest['other_text'])): ?>
                                    &mdash; <?= e($interest['other_text']) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </dd>

            <dt class="col-sm-3"><i class="bi bi-arrow-repeat text-muted me-1"></i> Follow-up Method</dt>
            <dd class="col-sm-9"><?= e(followUpLabel($submission['follow_up_method'])) ?></dd>

            <dt class="col-sm-3"><i class="bi bi-envelope text-muted me-1"></i> Email</dt>
            <dd class="col-sm-9"><?= $submission['email'] ? e($submission['email']) : '<span class="text-muted">&mdash;</span>' ?></dd>
        </dl>

        <hr>

        <label for="message" class="form-label fw-semibold">
            <i class="bi bi-chat-text text-muted me-1"></i> Message
        </label>
        <form method="post" action="/admin/submissions/update_message.php">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $submission['id'] ?>">
            <textarea class="form-control mb-2" id="message" name="message" rows="3"
                      maxlength="2000"><?= e($submission['message'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-check-lg"></i> Save Message
            </button>
        </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../../includes/admin_footer.php'; ?>
