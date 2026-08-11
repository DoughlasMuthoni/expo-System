<?php

declare(strict_types=1);

/**
 * Nothing lives at the web root itself — visitors always land on an
 * expo-specific URL (public/expo/?slug=...), and staff use /admin/.
 * Until the public form exists (Module 3), send root traffic to admin login.
 */
header('Location: /admin/login.php');
exit;
