<?php
require_once '../includes/role.php';
requireRole('admin');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/dashboard_helpers.php';

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
if ($from === '') $from = date('Y-m-01');
if ($to === '') $to = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');
if ($from > $to) { [$from, $to] = [$to, $from]; }

$bookingStmt = $pdo->prepare("SELECT
    COUNT(*) AS total_bookings,
    COALESCE(SUM(status IN ('confirmed','waiting','in_progress')),0) AS processing,
    COALESCE(SUM(status = 'waiting_payment'),0) AS waiting_payment,
    COALESCE(SUM(status = 'paid'),0) AS paid,
    COALESCE(SUM(status = 'cancelled'),0) AS cancelled
    FROM bookings WHERE DATE(booking_date) BETWEEN ? AND ?");
$bookingStmt->execute([$from, $to]);
$stats = $bookingStmt->fetch();

$paymentStmt = $pdo->prepare("SELECT
    COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) AS revenue,
    COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) AS pending_amount,
    COALESCE(SUM(CASE WHEN status='failed' THEN amount ELSE 0 END),0) AS failed_amount,
    COALESCE(SUM(status='paid'),0) AS paid_count,
    COALESCE(SUM(status='pending'),0) AS pending_count,
    COALESCE(SUM(status='failed'),0) AS failed_count
    FROM payments WHERE DATE(COALESCE(paid_at, submitted_at, created_at)) BETWEEN ? AND ?");
$paymentStmt->execute([$from, $to]);
$payments = $paymentStmt->fetch();

$byServiceStmt = $pdo->prepare("SELECT s.name, COUNT(b.id) total,
    COALESCE(SUM(CASE WHEN b.status='paid' THEN b.service_price ELSE 0 END),0) service_revenue
    FROM services s LEFT JOIN bookings b ON b.service_id=s.id AND DATE(b.booking_date) BETWEEN ? AND ?
    GROUP BY s.id, s.name ORDER BY total DESC, s.name ASC");
$byServiceStmt->execute([$from, $to]);
$byService = $byServiceStmt->fetchAll();

$dailyStmt = $pdo->prepare("SELECT DATE(p.paid_at) report_date, COALESCE(SUM(p.amount),0) total
    FROM payments p WHERE p.status='paid' AND p.paid_at IS NOT NULL AND DATE(p.paid_at) BETWEEN ? AND ?
    GROUP BY DATE(p.paid_at) ORDER BY DATE(p.paid_at) ASC");
$dailyStmt->execute([$from, $to]);
$daily = $dailyStmt->fetchAll();
$dailyMap = [];
foreach ($daily as $row) $dailyMap[$row['report_date']] = (float)$row['total'];
$maxDaily = dashboard_max(array_values($dailyMap));

$transactionsStmt = $pdo->prepare("SELECT p.id,p.amount,p.status,p.paid_at,p.submitted_at,b.id booking_id,u.name customer_name
    FROM payments p JOIN bookings b ON b.id=p.booking_id JOIN users u ON u.id=b.user_id
    WHERE DATE(COALESCE(p.paid_at,p.submitted_at,p.created_at)) BETWEEN ? AND ?
    ORDER BY COALESCE(p.paid_at,p.submitted_at,p.created_at) DESC LIMIT 20");
$transactionsStmt->execute([$from, $to]);
$transactions = $transactionsStmt->fetchAll();

$pageTitle='Laporan';
require '../includes/admin_header.php';
?>
<div class="report-head">
    <div>
        <span class="section-title">Keuangan & Operasional</span>
        <h2>Laporan Transaksi</h2>
        <p>Gunakan periode untuk melihat booking, pembayaran, pendapatan, dan layanan secara terukur.</p>
    </div>
    <div class="report-actions no-print"><button type="button" class="button secondary" onclick="window.print()">Cetak Laporan</button></div>
</div>

<section class="panel report-filter no-print">
    <form method="get" class="report-filter-form">
        <label>Dari<input type="date" name="from" value="<?= e($from) ?>"></label>
        <label>Sampai<input type="date" name="to" value="<?= e($to) ?>"></label>
        <button class="button" type="submit">Terapkan</button>
        <a class="button secondary" href="reports.php">Bulan Ini</a>
    </form>
</section>

<div class="stats-grid report-stats">
    <div class="stat-card"><span>Total Booking</span><strong><?= (int)$stats['total_bookings'] ?></strong><small>Dalam periode terpilih</small></div>
    <div class="stat-card"><span>Sedang Diproses</span><strong><?= (int)$stats['processing'] ?></strong><small>Booking aktif</small></div>
    <div class="stat-card"><span>Menunggu Pembayaran</span><strong><?= (int)$stats['waiting_payment'] ?></strong><small>Perlu ditindaklanjuti</small></div>
    <div class="stat-card"><span>Booking Lunas</span><strong><?= (int)$stats['paid'] ?></strong><small>Sudah selesai dibayar</small></div>
    <div class="stat-card"><span>Pembayaran Lunas</span><strong><?= (int)$payments['paid_count'] ?></strong><small>Transaksi terverifikasi</small></div>
    <div class="stat-card report-revenue-card"><span>Pendapatan Lunas</span><strong><?= rupiah($payments['revenue']) ?></strong><small>Payment berstatus Lunas</small></div>
</div>

<div class="report-grid">
<section class="panel report-chart-panel">
    <div class="panel-heading"><div><span class="section-title">Pendapatan</span><h2>Tren Harian</h2></div><span class="report-period"><?= e(date('d M Y', strtotime($from))) ?> — <?= e(date('d M Y', strtotime($to))) ?></span></div>
    <div class="report-bars">
    <?php if ($daily): foreach ($daily as $row): $value=(float)$row['total']; $height=dashboard_percent((float)$value,$maxDaily,7); ?>
        <div class="report-bar-item" title="<?= e(date('d M Y',strtotime($row['report_date']))) ?>: <?= e(rupiah($value)) ?>">
            <div class="report-bar-value"><?= $value >= 1000000 ? e(number_format($value/1000000,1,',','.')).' jt' : e(number_format($value/1000,0,',','.')).' rb' ?></div>
            <div class="report-bar-track"><div class="report-bar-fill" style="height:<?= $height ?>%"></div></div>
            <span><?= e(date('d/m',strtotime($row['report_date']))) ?></span>
        </div>
    <?php endforeach; else: ?>
        <div class="report-empty">Belum ada pembayaran lunas pada periode ini.</div>
    <?php endif; ?>
    </div>
</section>

<section class="panel report-payment-summary">
    <div class="panel-heading"><div><span class="section-title">Pembayaran</span><h2>Ringkasan Status</h2></div></div>
    <div class="report-status-list">
        <div><span class="report-status-dot paid"></span><div><strong>Lunas</strong><small><?= (int)$payments['paid_count'] ?> transaksi</small></div><b><?= rupiah($payments['revenue']) ?></b></div>
        <div><span class="report-status-dot pending"></span><div><strong>Menunggu Verifikasi</strong><small><?= (int)$payments['pending_count'] ?> transaksi</small></div><b><?= rupiah($payments['pending_amount']) ?></b></div>
        <div><span class="report-status-dot failed"></span><div><strong>Ditolak</strong><small><?= (int)$payments['failed_count'] ?> transaksi</small></div><b><?= rupiah($payments['failed_amount']) ?></b></div>
    </div>
    <div class="report-note">Nilai pending dan ditolak ditampilkan sebagai informasi transaksi pada periode laporan.</div>
</section>
</div>

<section class="panel">
<div class="panel-heading"><div><span class="section-title">Layanan</span><h2>Performa Layanan</h2></div></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Layanan</th><th>Booking</th><th>Pendapatan Jasa Lunas</th></tr></thead><tbody>
<?php foreach($byService as $x): ?><tr><td><?= e($x['name']) ?></td><td><?= (int)$x['total'] ?></td><td><?= rupiah($x['service_revenue']) ?></td></tr><?php endforeach; ?>
<?php if(!$byService): ?><tr><td colspan="3" class="empty">Belum ada data layanan.</td></tr><?php endif; ?></tbody></table></div>
</section>

<section class="panel">
<div class="panel-heading"><div><span class="section-title">Transaksi</span><h2>Aktivitas Pembayaran</h2></div></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Payment</th><th>Booking</th><th>Customer</th><th>Nominal</th><th>Waktu</th><th>Status</th></tr></thead><tbody>
<?php foreach($transactions as $x): $eventDate=$x['paid_at'] ?: ($x['submitted_at'] ?: null); ?>
<tr><td>#<?= (int)$x['id'] ?></td><td>#<?= (int)$x['booking_id'] ?></td><td><?= e($x['customer_name']) ?></td><td><?= rupiah($x['amount']) ?></td><td><?= $eventDate ? e(date('d-m-Y H:i',strtotime($eventDate))) : '-' ?></td><td><span class="badge badge-<?= e($x['status']) ?>"><?= e(status_label($x['status'])) ?></span></td></tr>
<?php endforeach; ?>
<?php if(!$transactions): ?><tr><td colspan="6" class="empty">Belum ada transaksi pada periode ini.</td></tr><?php endif; ?></tbody></table></div>
</section>
<?php require '../includes/admin_footer.php'; ?>
