<?php
if (!isset($pageTitle)) $pageTitle = 'Customer - ServisKu';
require_once __DIR__ . '/icons.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($pageTitle) ?> | ServisKu</title>
<link rel="stylesheet" href="../assets/css/style.css?v=37">
<script defer src="../assets/js/app.js"></script>
</head>
<body class="workspace-customer">
<div class="app-shell">
<?php render_shell([
    'subtitle' => 'Customer Portal', 'crumb' => 'ServisKu / Customer', 'fallback' => 'Customer',
    'nav' => [
        ['dashboard.php', 'Dashboard', 'dashboard'],
        ['profile.php', 'Profil', 'user'],
        ['vehicles.php', 'Kendaraan', 'car'],
        ['booking.php', 'Booking Servis', 'calendar'],
        ['history.php', 'Riwayat Servis', 'history'],
        ['notifications.php', 'Notifikasi', 'bell'],
    ],
], $current, $pageTitle, $pdo ?? null); ?>
