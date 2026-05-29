<?php

declare(strict_types=1);

if (getAuthUser()) {
    require __DIR__ . '/../journal/index.php';
} else {
    header('Location: /login');
    exit;
}
