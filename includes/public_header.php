<?php
/**
 * Shared header for public visitor-facing pages under public/expo/.
 * Expects $pageTitle (string). Lives outside web root.
 */
$pageTitle ??= 'Waterlift Solar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Waterlift Solar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/brand.css" rel="stylesheet">
    <link href="/assets/css/public.css" rel="stylesheet">
</head>
<body class="public-page">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-6 py-4">
            <div class="text-center mb-4">
                <img src="/assets/img/waterlift-logo.jpeg" alt="Waterlift Solar" class="img-fluid public-logo">
            </div>
