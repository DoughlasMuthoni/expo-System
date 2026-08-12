<?php
/**
 * Shared create/edit form for expos.
 * Expects: $expo (array), $errors (array), $formAction (string), $submitLabel (string).
 */
?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= e($formAction) ?>" novalidate>
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Expo Name</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="150" required
                       value="<?= e($expo['name']) ?>">
            </div>
            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" maxlength="150"
                       value="<?= e($expo['slug']) ?>" placeholder="auto-generated from name if left blank">
                <div class="form-text">
                    Used in the public URL and QR code. Lowercase letters, numbers, hyphens only.
                    Changing this on an existing expo regenerates its QR code.
                </div>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" maxlength="255"
                       value="<?= e($expo['location'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                           value="<?= e($expo['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?= e($expo['end_date'] ?? '') ?>">
                </div>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                       <?= !empty($expo['is_active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active (visitors can submit the form)</label>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> <?= e($submitLabel) ?>
            </button>
            <a href="/admin/expos/index.php" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>

<script>
// Convenience only: auto-fill slug from name until the admin edits slug directly.
// Server-side validation/uniqueness in Expo::uniqueSlug() is authoritative.
(function () {
    var nameEl = document.getElementById('name');
    var slugEl = document.getElementById('slug');
    var slugTouched = slugEl.value.trim() !== '';

    slugEl.addEventListener('input', function () { slugTouched = true; });
    nameEl.addEventListener('input', function () {
        if (slugTouched) return;
        slugEl.value = nameEl.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
})();
</script>
