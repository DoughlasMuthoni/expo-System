<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::logout();
header('Location: /admin/login.php');
exit;
