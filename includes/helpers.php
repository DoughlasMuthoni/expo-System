<?php

declare(strict_types=1);

/**
 * Small shared utilities used across admin and public pages.
 */

/** Shorthand for escaping output — never echo user input raw. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function followUpLabel(string $value): string
{
    return match ($value) {
        'phone_call' => 'Phone Call',
        'whatsapp'   => 'WhatsApp',
        'email'      => 'Email',
        default      => $value,
    };
}

/** Lowercase, hyphenated slug from arbitrary text (e.g. an expo name). */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    return trim($text, '-');
}

/** One-shot session flash message, read once then cleared. */
function flashSet(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flashGet(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Shared create/edit validation for the expo admin form.
 * $expo holds trimmed raw input (name, slug, location, start_date, end_date).
 */
function validateExpoInput(array $expo): array
{
    $errors = [];

    if (!Validator::required($expo['name']) || !Validator::maxLength($expo['name'], 150)) {
        $errors[] = 'Expo name is required (max 150 characters).';
    }

    if ($expo['location'] !== '' && !Validator::maxLength($expo['location'], 255)) {
        $errors[] = 'Location must be 255 characters or fewer.';
    }

    if (!Validator::dateOrEmpty($expo['start_date'])) {
        $errors[] = 'Start date is not valid.';
    }

    if (!Validator::dateOrEmpty($expo['end_date'])) {
        $errors[] = 'End date is not valid.';
    }

    if ($expo['start_date'] !== '' && $expo['end_date'] !== '' && $expo['end_date'] < $expo['start_date']) {
        $errors[] = 'End date cannot be before start date.';
    }

    if ($expo['slug'] !== '' && !Validator::slugFormat(slugify($expo['slug']))) {
        $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
    }

    return $errors;
}
