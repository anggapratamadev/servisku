<?php
require_once __DIR__ . '/auth.php';

function requireRole(string $role): void
{
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}
