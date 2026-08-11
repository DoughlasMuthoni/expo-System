<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';

Auth::requireAuth();

$format = ($_GET['format'] ?? 'csv') === 'xlsx' ? 'xlsx' : 'csv';

$expoFilterId = isset($_GET['expo_id']) && $_GET['expo_id'] !== '' ? (int) $_GET['expo_id'] : null;
$expo = $expoFilterId !== null ? Expo::find($expoFilterId) : null;

// An expo_id that doesn't exist just exports everything rather than erroring.
if ($expoFilterId !== null && $expo === null) {
    $expoFilterId = null;
}

$duplicatesOnly = isset($_GET['duplicates_only']);
$submissions = Submission::allWithExpo($expoFilterId, $duplicatesOnly);

$slugPart = $expo !== null ? $expo['slug'] : 'all-expos';
$filenameBase = 'waterlift-submissions-' . $slugPart . ($duplicatesOnly ? '-duplicates' : '') . '-' . date('Y-m-d');

if ($format === 'xlsx') {
    Exporter::streamXlsx($submissions, $filenameBase . '.xlsx');
} else {
    Exporter::streamCsv($submissions, $filenameBase . '.csv');
}
exit;
