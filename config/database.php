<?php
/**
 * PDO database bootstrap.
 * Local Laragon defaults are provided for development only. Production should
 * define DB_HOST, DB_NAME, DB_USER, DB_PASSWORD and APP_ENV=production.
 * Schema creation/migrations are intentionally not run on every web request.
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbname = getenv('DB_NAME') ?: 'servisku';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') !== false ? (string) getenv('DB_PASSWORD') : '';
$appEnv = strtolower((string) (getenv('APP_ENV') ?: 'local'));
$appDebug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);

if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

if (!function_exists('servisku_error_message')) {
    function servisku_error_message(Throwable $error, string $fallback = 'Terjadi kesalahan. Silakan coba lagi.') : string
    {
        $env = strtolower((string) (getenv('APP_ENV') ?: 'local'));
        $debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);
        if ($debug && $env !== 'production') {
            return $error->getMessage();
        }
        if ($error instanceof PDOException || $error instanceof Error) {
            error_log('[ServisKu] Request failed: ' . $error->getMessage());
            return $fallback;
        }
        return $error->getMessage();
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]
    );


} catch (Throwable $e) {
    error_log('[ServisKu] Database initialization failed: ' . $e->getMessage());
    http_response_code(500);
    if ($appEnv !== 'production' && $appDebug) {
        exit('Koneksi database gagal. Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
    exit('Aplikasi sedang mengalami gangguan. Silakan coba lagi nanti.');
}
