<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/auth.php';

clearAuthCookies();
header('Location: /login');
exit;
