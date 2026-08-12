<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
    flashSet('Invalid request.', 'error');
    redirect('/admin/submissions/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$submission = $id > 0 ? Submission::find($id) : null;

if ($submission === null) {
    flashSet('Submission not found.', 'error');
    redirect('/admin/submissions/index.php');
}

$message = trim((string) ($_POST['message'] ?? ''));

if (!Validator::maxLength($message, 2000)) {
    flashSet('Message is too long (2000 characters max).', 'error');
    redirect('/admin/submissions/view.php?id=' . $id);
}

Submission::updateMessage($id, $message !== '' ? $message : null);

flashSet('Message updated.');
redirect('/admin/submissions/view.php?id=' . $id);
