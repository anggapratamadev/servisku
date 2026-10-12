<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($v): string { return 'Rp '.number_format((float)$v, 0, ',', '.'); }
function app_base_url(): string
{
    $configured = trim((string) (getenv('APP_BASE_URL') ?: 'http://localhost/servisku'));
    return rtrim($configured, '/');
}

function status_label(string $s): string
{
    $labels = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'waiting' => 'Menunggu Servis',
        'in_progress' => 'Sedang Dikerjakan',
        'waiting_payment' => 'Menunggu Pembayaran',
        'paid' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    return $labels[$s] ?? ucwords(str_replace('_', ' ', $s));
}

function payment_status_label(string $s): string
{
    $labels = [
        'pending' => 'Menunggu Verifikasi',
        'paid' => 'Lunas',
        'failed' => 'Ditolak',
    ];

    return $labels[$s] ?? ucwords(str_replace('_', ' ', $s));
}


function log_activity(PDO $pdo, string $action, string $description = '', ?string $entityType = null, ?int $entityId = null, ?int $userId = null): void
{
    try {
        $uid = $userId ?? (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
        $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$uid ?: null, $action, $entityType, $entityId ?: null, $description !== '' ? $description : null]);
    } catch (Throwable $e) {
        // Activity logging must never block the main application workflow.
    }
}


function notify_user(PDO $pdo, int $userId, string $title, string $message, string $type='info', ?string $entityType=null, ?int $entityId=null): void
{
    if ($userId < 1) return;
    try {
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, title, message, type, entity_type, entity_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $title, $message, $type, $entityType, $entityId ?: null]);
    } catch (Throwable $e) {
        // Notifications must never block the main workflow.
    }
}

function notify_role(PDO $pdo, string $role, string $title, string $message, string $type='info', ?string $entityType=null, ?int $entityId=null): void
{
    try {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE role=?');
        $stmt->execute([$role]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            notify_user($pdo, (int)$userId, $title, $message, $type, $entityType, $entityId);
        }
    } catch (Throwable $e) {
        // Notifications must never block the main workflow.
    }
}

function notification_unread_count(PDO $pdo, ?int $userId=null): int
{
    $uid = $userId ?? (int)($_SESSION['user_id'] ?? 0);
    if ($uid < 1) return 0;
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
        $stmt->execute([$uid]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}
