<?php
// ServisKu read-only JSON API: one booking with role-based access.
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

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    api_json(['success' => false, 'message' => 'Parameter id booking wajib diisi.'], 400);
}

$userId = (int) $_SESSION['user_id'];
$role = (string) ($_SESSION['role'] ?? 'customer');

try {
    $sql = "SELECT b.id, b.booking_date, b.status, b.complaint, b.created_at,
                   u.name customer_name, u.email customer_email,
                   v.plate_number, v.brand, v.model, v.year,
                   s.name service_name, s.description service_description, s.price service_price,
                   mu.name mechanic_name, m.specialization mechanic_specialization,
                   p.status payment_status, p.amount payment_amount
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN vehicles v ON v.id = b.vehicle_id
            JOIN services s ON s.id = b.service_id
            LEFT JOIN mechanics m ON m.id = b.mechanic_id
            LEFT JOIN users mu ON mu.id = m.user_id
            LEFT JOIN payments p ON p.booking_id = b.id
            WHERE b.id = ?";

    $params = [$id];
    if ($role === 'customer') {
        $sql .= ' AND b.user_id = ?';
        $params[] = $userId;
    } elseif ($role === 'mechanic') {
        $sql .= ' AND m.user_id = ?';
        $params[] = $userId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    if (!$row) {
        api_json(['success' => false, 'message' => 'Booking tidak ditemukan atau tidak dapat diakses.'], 404);
    }

    $partsStmt = $pdo->prepare("SELECT sp.name, sp.price, spp.quantity, spp.price line_price
                                FROM service_parts spp
                                JOIN spareparts sp ON sp.id = spp.sparepart_id
                                JOIN service_records sr ON sr.id = spp.service_record_id
                                WHERE sr.booking_id = ?
                                ORDER BY spp.id ASC");
    $partsStmt->execute([$id]);
    $parts = [];
    $partsTotal = 0.0;
    foreach ($partsStmt->fetchAll() as $part) {
        $lineTotal = (float) $part['line_price'] * (int) $part['quantity'];
        $partsTotal += $lineTotal;
        $parts[] = [
            'name' => $part['name'],
            'quantity' => (int) $part['quantity'],
            'unit_price' => (float) $part['line_price'],
            'total' => $lineTotal,
        ];
    }

    $serviceRecordStmt = $pdo->prepare("SELECT sr.inspection, sr.diagnosis, sr.action_taken, sr.notes, sr.completed_at
                                        FROM service_records sr WHERE sr.booking_id = ? ORDER BY sr.id DESC LIMIT 1");
    $serviceRecordStmt->execute([$id]);
    $record = $serviceRecordStmt->fetch() ?: null;

    api_json([
        'success' => true,
        'data' => [
            'id' => (int) $row['id'],
            'booking_code' => 'BK-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT),
            'booking_date' => $row['booking_date'],
            'status' => $row['status'],
            'complaint' => $row['complaint'],
            'created_at' => $row['created_at'],
            'customer' => [
                'name' => $row['customer_name'],
                'email' => $role === 'customer' ? $row['customer_email'] : null,
            ],
            'vehicle' => [
                'plate_number' => $row['plate_number'],
                'brand' => $row['brand'],
                'model' => $row['model'],
                'year' => $row['year'] !== null ? (int) $row['year'] : null,
            ],
            'service' => [
                'name' => $row['service_name'],
                'description' => $row['service_description'],
                'price' => (float) $row['service_price'],
            ],
            'mechanic' => $row['mechanic_name'] ? [
                'name' => $row['mechanic_name'],
                'specialization' => $row['mechanic_specialization'],
            ] : null,
            'service_record' => $record,
            'spareparts' => $parts,
            'totals' => [
                'service' => (float) $row['service_price'],
                'spareparts' => $partsTotal,
                'estimated_total' => (float) $row['service_price'] + $partsTotal,
            ],
            'payment' => [
                'status' => $row['payment_status'],
                'amount' => $row['payment_amount'] !== null ? (float) $row['payment_amount'] : null,
            ],
        ],
    ]);
} catch (Throwable $e) {
    api_json(['success' => false, 'message' => 'Terjadi kesalahan saat mengambil data.'], 500);
}
