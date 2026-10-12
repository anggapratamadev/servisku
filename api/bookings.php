<?php
// ServisKu read-only JSON API: authenticated bookings list.
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function api_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_json(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan GET.'], 405);
}

if (!isset($_SESSION['user_id'])) {
    api_json(['success' => false, 'message' => 'Autentikasi diperlukan.'], 401);
}

$userId = (int) $_SESSION['user_id'];
$role = (string) ($_SESSION['role'] ?? 'customer');

try {
    if ($role === 'admin') {
        $sql = "SELECT b.id, b.booking_date, b.status, b.complaint, b.created_at,
                       u.name customer_name, v.plate_number, v.brand, v.model,
                       s.name service_name, s.price service_price,
                       mu.name mechanic_name
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN vehicles v ON v.id = b.vehicle_id
                JOIN services s ON s.id = b.service_id
                LEFT JOIN mechanics m ON m.id = b.mechanic_id
                LEFT JOIN users mu ON mu.id = m.user_id
                ORDER BY b.booking_date DESC, b.id DESC";
        $stmt = $pdo->query($sql);
    } elseif ($role === 'mechanic') {
        $stmt = $pdo->prepare("SELECT b.id, b.booking_date, b.status, b.complaint, b.created_at,
                                      u.name customer_name, v.plate_number, v.brand, v.model,
                                      s.name service_name, s.price service_price,
                                      mu.name mechanic_name
                               FROM bookings b
                               JOIN users u ON u.id = b.user_id
                               JOIN vehicles v ON v.id = b.vehicle_id
                               JOIN services s ON s.id = b.service_id
                               JOIN mechanics m ON m.id = b.mechanic_id AND m.user_id = ?
                               LEFT JOIN users mu ON mu.id = m.user_id
                               ORDER BY b.booking_date DESC, b.id DESC");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT b.id, b.booking_date, b.status, b.complaint, b.created_at,
                                      u.name customer_name, v.plate_number, v.brand, v.model,
                                      s.name service_name, s.price service_price,
                                      mu.name mechanic_name
                               FROM bookings b
                               JOIN users u ON u.id = b.user_id
                               JOIN vehicles v ON v.id = b.vehicle_id
                               JOIN services s ON s.id = b.service_id
                               LEFT JOIN mechanics m ON m.id = b.mechanic_id
                               LEFT JOIN users mu ON mu.id = m.user_id
                               WHERE b.user_id = ?
                               ORDER BY b.booking_date DESC, b.id DESC");
        $stmt->execute([$userId]);
    }

    $rows = $stmt->fetchAll();
    $bookings = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'booking_code' => 'BK-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT),
            'booking_date' => $row['booking_date'],
            'status' => $row['status'],
            'complaint' => $row['complaint'],
            'created_at' => $row['created_at'],
            'customer' => $row['customer_name'],
            'vehicle' => [
                'plate_number' => $row['plate_number'],
                'brand' => $row['brand'],
                'model' => $row['model'],
            ],
            'service' => [
                'name' => $row['service_name'],
                'price' => (float) $row['service_price'],
            ],
            'mechanic' => $row['mechanic_name'],
        ];
    }, $rows);

    api_json([
        'success' => true,
        'data' => $bookings,
        'meta' => [
            'count' => count($bookings),
            'role' => $role,
        ],
    ]);
} catch (Throwable $e) {
    api_json(['success' => false, 'message' => 'Terjadi kesalahan saat mengambil data.'], 500);
}
