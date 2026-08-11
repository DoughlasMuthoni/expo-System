<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$expo = $slug !== '' ? Expo::findBySlug($slug) : null;

if ($expo === null) {
    http_response_code(404);
    $pageTitle = 'Expo Not Found';
    require __DIR__ . '/../../includes/public_header.php';
    ?>
    <div class="card shadow-sm">
        <div class="card-body text-center p-4">
            <h1 class="h4">We couldn't find that expo</h1>
            <p class="text-muted mb-0">Please check the QR code or link and try again.</p>
        </div>
    </div>
    <?php
    require __DIR__ . '/../../includes/public_footer.php';
    exit;
}

if (!$expo['is_active']) {
    $pageTitle = $expo['name'];
    require __DIR__ . '/../../includes/public_header.php';
    ?>
    <div class="card shadow-sm">
        <div class="card-body text-center p-4">
            <h1 class="h4"><?= e($expo['name']) ?></h1>
            <p class="text-muted mb-0">
                This expo isn't currently accepting submissions. Thank you for your interest —
                please check back later or speak to a Waterlift Solar representative directly.
            </p>
        </div>
    </div>
    <?php
    require __DIR__ . '/../../includes/public_footer.php';
    exit;
}

// Never trust a client-supplied expo id — $expo['id'] came from the slug lookup above, not from POST.
$interests = Interest::active();
$activeInterestIds = array_column($interests, 'id');

$otherInterestId = null;
foreach ($interests as $interest) {
    if ($interest['name'] === 'Other') {
        $otherInterestId = (int) $interest['id'];
        break;
    }
}

$errors = [];
$submitted = [
    'full_name'        => '',
    'phone'            => '',
    'project_location' => '',
    'follow_up_method' => '',
    'email'            => '',
    'message'          => '',
    'other_text'       => '',
    'interest_ids'     => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (trim((string) ($_POST['company_website'] ?? '')) !== '') {
        // Honeypot tripped — pretend success, don't store or tip off the bot.
        redirect('/expo/success.php?slug=' . urlencode($slug));
    } else {
        $submitted['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
        $submitted['phone'] = trim((string) ($_POST['phone'] ?? ''));
        $submitted['project_location'] = trim((string) ($_POST['project_location'] ?? ''));
        $submitted['follow_up_method'] = trim((string) ($_POST['follow_up_method'] ?? ''));
        $submitted['email'] = trim((string) ($_POST['email'] ?? ''));
        $submitted['message'] = trim((string) ($_POST['message'] ?? ''));
        $submitted['other_text'] = trim((string) ($_POST['other_text'] ?? ''));

        $postedInterestIds = array_map('intval', $_POST['interests'] ?? []);
        $submitted['interest_ids'] = array_values(array_intersect($postedInterestIds, $activeInterestIds));

        if (!Validator::required($submitted['full_name']) || !Validator::maxLength($submitted['full_name'], 150)) {
            $errors[] = 'Full name is required.';
        }

        if (!Validator::required($submitted['phone']) || !Validator::maxLength($submitted['phone'], 30)) {
            $errors[] = 'Phone number is required.';
        }

        if (!Validator::required($submitted['project_location']) || !Validator::maxLength($submitted['project_location'], 255)) {
            $errors[] = 'Project location is required.';
        }

        if (empty($submitted['interest_ids'])) {
            $errors[] = 'Please select at least one interest.';
        }

        if (
            $otherInterestId !== null
            && in_array($otherInterestId, $submitted['interest_ids'], true)
            && $submitted['other_text'] === ''
        ) {
            $errors[] = 'Please describe your "Other" interest.';
        }

        if (!in_array($submitted['follow_up_method'], ['phone_call', 'whatsapp', 'email'], true)) {
            $errors[] = 'Please choose a follow-up method.';
        }

        if ($submitted['email'] !== '' && !Validator::isEmail($submitted['email'])) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (!Validator::maxLength($submitted['message'], 2000)) {
            $errors[] = 'Message is too long.';
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        if (empty($errors) && $ipAddress !== null && Submission::recentlySubmitted($ipAddress, (int) $expo['id'])) {
            $errors[] = 'You just submitted this form. Please wait a moment before submitting again.';
        }

        if (empty($errors)) {
            Submission::create(
                (int) $expo['id'],
                [
                    'full_name'        => $submitted['full_name'],
                    'phone'            => $submitted['phone'],
                    'project_location' => $submitted['project_location'],
                    'follow_up_method' => $submitted['follow_up_method'],
                    'email'            => $submitted['email'],
                    'message'          => $submitted['message'],
                    'ip_address'       => $ipAddress,
                ],
                $submitted['interest_ids'],
                $submitted['other_text'] !== '' ? $submitted['other_text'] : null
            );

            redirect('/expo/success.php?slug=' . urlencode($slug));
        }
    }
}

$pageTitle = $expo['name'];
require __DIR__ . '/../../includes/public_header.php';
?>
<h1 class="h4 text-center mb-1"><?= e($expo['name']) ?></h1>
<?php if (!empty($expo['location'])): ?>
    <p class="text-center text-muted mb-4"><?= e($expo['location']) ?></p>
<?php else: ?>
    <div class="mb-4"></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/expo/index.php?slug=<?= urlencode($slug) ?>" novalidate>
    <?= Csrf::field() ?>

    <div class="hp-field" aria-hidden="true">
        <label for="company_website">Leave this field blank</label>
        <input type="text" id="company_website" name="company_website" tabindex="-1" autocomplete="off">
    </div>

    <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" required maxlength="150"
               value="<?= e($submitted['full_name']) ?>">
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" required maxlength="30"
               value="<?= e($submitted['phone']) ?>">
    </div>

    <div class="mb-3">
        <label for="project_location" class="form-label">Project Location</label>
        <input type="text" class="form-control" id="project_location" name="project_location" required maxlength="255"
               value="<?= e($submitted['project_location']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label d-block">I'm Interested In</label>
        <?php foreach ($interests as $interest): ?>
            <?php $checked = in_array((int) $interest['id'], $submitted['interest_ids'], true); ?>
            <div class="form-check">
                <input type="checkbox" class="form-check-input interest-checkbox"
                       id="interest_<?= (int) $interest['id'] ?>" name="interests[]"
                       value="<?= (int) $interest['id'] ?>"
                       data-is-other="<?= $interest['name'] === 'Other' ? '1' : '0' ?>"
                       <?= $checked ? 'checked' : '' ?>>
                <label class="form-check-label" for="interest_<?= (int) $interest['id'] ?>"><?= e($interest['name']) ?></label>
            </div>
        <?php endforeach; ?>
        <div class="mt-2 <?= ($otherInterestId !== null && in_array($otherInterestId, $submitted['interest_ids'], true)) ? '' : 'd-none' ?>"
             id="other-text-wrap">
            <input type="text" class="form-control" name="other_text" maxlength="255" placeholder="Please describe"
                   value="<?= e($submitted['other_text']) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Preferred Follow-up Method</label>
        <?php foreach (['phone_call' => 'Phone Call', 'whatsapp' => 'WhatsApp', 'email' => 'Email'] as $value => $label): ?>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="followup_<?= e($value) ?>" name="follow_up_method"
                       value="<?= e($value) ?>" required <?= $submitted['follow_up_method'] === $value ? 'checked' : '' ?>>
                <label class="form-check-label" for="followup_<?= e($value) ?>"><?= e($label) ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email <span class="text-muted">(optional)</span></label>
        <input type="email" class="form-control" id="email" name="email" maxlength="150"
               value="<?= e($submitted['email']) ?>">
    </div>

    <div class="mb-4">
        <label for="message" class="form-label">Message <span class="text-muted">(optional)</span></label>
        <textarea class="form-control" id="message" name="message" rows="3" maxlength="2000"><?= e($submitted['message']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-submit">Submit</button>
</form>

<script>
(function () {
    var otherCheckbox = document.querySelector('.interest-checkbox[data-is-other="1"]');
    var wrap = document.getElementById('other-text-wrap');
    if (!otherCheckbox || !wrap) { return; }
    otherCheckbox.addEventListener('change', function () {
        wrap.classList.toggle('d-none', !otherCheckbox.checked);
    });
})();
</script>
<?php
require __DIR__ . '/../../includes/public_footer.php';
