<?php
if (!isset($pageTitle)) $pageTitle = 'Admin - ServisKu';
require_once __DIR__ . '/icons.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | ServisKu</title>
<link rel="stylesheet" href="../assets/css/style.css?v=41">
<script defer src="../assets/js/app.js"></script>
</head>
<body class="workspace-admin">
<div class="app-shell">
<?php render_shell([
    'subtitle' => 'Vehicle Service Management', 'crumb' => 'ServisKu / Admin', 'fallback' => 'Admin',
    'nav' => [
        ['dashboard.php', 'Dashboard', 'dashboard'],
        ['customers.php', 'Customer', 'users'],
        ['vehicles.php', 'Kendaraan', 'car'],
        ['services.php', 'Layanan', 'tool'],
        ['mechanics.php', 'Mekanik', 'wrench'],
        ['bookings.php', 'Booking', 'calendar'],
        ['spareparts.php', 'Sparepart', 'box'],
        ['payments.php', 'Pembayaran', 'wallet'],
        ['reports.php', 'Laporan', 'chart'],
        ['activity.php', 'Activity Log', 'log'],
        ['notifications.php', 'Notifikasi', 'bell'],
    ],
], $current, $pageTitle, $pdo ?? null); ?>
