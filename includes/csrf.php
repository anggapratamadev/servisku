<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function csrf_token(): string { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) { http_response_code(419); exit('Permintaan tidak valid. Silakan kembali dan coba lagi.'); } }
