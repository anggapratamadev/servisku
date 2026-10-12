<?php
if (!isset($pageTitle)) $pageTitle = 'Mekanik - ServisKu';
require_once __DIR__ . '/icons.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | ServisKu</title>
<link rel="stylesheet" href="../assets/css/style.css?v=37">
<script defer src="../assets/js/app.js"></script>
</head>
<body class="workspace-mechanic">
<div class="app-shell">
<?php render_shell([
    'subtitle' => 'Mechanic Workspace', 'crumb' => 'ServisKu / Mekanik', 'fallback' => 'Mekanik',
    'nav' => [
        ['dashboard.php', 'Dashboard', 'dashboard'],
        ['notifications.php', 'Notifikasi', 'bell'],
    ],
], $current, $pageTitle, $pdo ?? null); ?>
