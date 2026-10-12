<?php
require_once '../includes/role.php';
requireRole('admin');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/dashboard_helpers.php';

$stats = [
    'customers' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'vehicles' => (int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn(),
    'mechanics' => (int)$pdo->query("SELECT COUNT(*) FROM mechanics WHERE status <> 'inactive'")->fetchColumn(),
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'process' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','waiting','in_progress')")->fetchColumn(),
    'finished' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'paid'")->fetchColumn(),
    'waiting_payment' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'waiting_payment'")->fetchColumn(),
    'revenue' => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'paid'")->fetchColumn(),
];

$recent = $pdo->query("SELECT b.id,b.booking_date,b.status,u.name customer_name,v.plate_number,s.name service_name
    FROM bookings b JOIN users u ON u.id=b.user_id JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id
    ORDER BY b.created_at DESC LIMIT 8")->fetchAll();

$topServices = $pdo->query("SELECT s.name, COUNT(b.id) total
    FROM services s LEFT JOIN bookings b ON b.service_id=s.id
    GROUP BY s.id,s.name ORDER BY total DESC,s.name ASC LIMIT 5")->fetchAll();

$monthly = $pdo->query("SELECT MONTH(p.paid_at) month_number, COALESCE(SUM(p.amount),0) total
    FROM payments p WHERE p.status='paid' AND p.paid_at IS NOT NULL AND YEAR(p.paid_at)=YEAR(CURDATE())
    GROUP BY MONTH(p.paid_at) ORDER BY MONTH(p.paid_at)")->fetchAll();
$monthlyMap = [];
foreach ($monthly as $item) $monthlyMap[(int)$item['month_number']] = (float)$item['total'];
$monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$maxRevenue = dashboard_max(array_values($monthlyMap));
$statusRows = $pdo->query("SELECT status, COUNT(*) total FROM bookings GROUP BY status")->fetchAll();
$statusMap = [];
foreach ($statusRows as $row) $statusMap[$row['status']] = (int)$row['total'];
$statusItems = [
    ['label'=>'Pending','key'=>'pending','class'=>'pending'],
    ['label'=>'Proses','key'=>'process','class'=>'process'],
    ['label'=>'Menunggu Bayar','key'=>'waiting_payment','class'=>'payment'],
    ['label'=>'Selesai','key'=>'paid','class'=>'done'],
];
$processCount = ($statusMap['confirmed']??0)+($statusMap['waiting']??0)+($statusMap['in_progress']??0);
$statusValues = ['pending'=>$statusMap['pending']??0,'process'=>$processCount,'waiting_payment'=>$statusMap['waiting_payment']??0,'paid'=>$statusMap['paid']??0];
$maxStatus = dashboard_max(array_values($statusValues));

$pageTitle = 'Dashboard Admin';
require '../includes/admin_header.php';
?>
<div class="admin-dashboard-head">
    <div>
        <span class="section-title">Overview</span>
        <h2>Ringkasan Operasional</h2>
        <p>Pantau customer, booking, pekerjaan servis, dan pembayaran dari satu halaman.</p>
    </div>
    <a class="button" href="bookings.php">Kelola Booking</a>
</div>

<div class="stats-grid admin-stats-grid">
<?php foreach ([
    ['customers','Customer','Total akun customer'],
    ['vehicles','Kendaraan','Total kendaraan'],
    ['mechanics','Mekanik Aktif','Siap menerima pekerjaan'],
    ['pending','Booking Pending','Perlu dikonfirmasi'],
    ['process','Sedang Diproses','Booking aktif'],
    ['waiting_payment','Menunggu Pembayaran','Perlu ditindaklanjuti'],
    ['finished','Selesai','Booking sudah lunas'],
] as $item): ?>
<div class="stat-card"><span><?= $label = $item[1] ?></span><strong><?= $stats[$item[0]] ?></strong><small><?= htmlspecialchars($item[2]) ?></small></div>
<?php endforeach; ?>
<div class="stat-card admin-revenue-card"><span>Pendapatan Lunas</span><strong>Rp <?= number_format($stats['revenue'],0,',','.') ?></strong><small>Total payment berstatus Lunas</small></div>
</div>

<div class="admin-analytics-grid">
<section class="panel admin-chart-panel">
    <div class="panel-heading"><div><span class="section-title">Keuangan</span><h2>Pendapatan Tahun Ini</h2></div><span class="admin-chart-note">Payment Lunas</span></div>
    <div class="admin-bars" aria-label="Grafik pendapatan bulanan">
    <?php for ($m=1;$m<=12;$m++): $value=$monthlyMap[$m]??0; $height=$value>0?dashboard_percent((float)$value,$maxRevenue,7):4; ?>
        <div class="admin-bar-item"><div class="admin-bar-value"><?= $value>0?'Rp '.number_format($value/1000000,1,',','.').' jt':'-' ?></div><div class="admin-bar-track"><div class="admin-bar-fill" style="height:<?= $height ?>%"></div></div><span><?= $monthNames[$m] ?></span></div>
    <?php endfor; ?>
    </div>
</section>

<section class="panel admin-chart-panel">
    <div class="panel-heading"><div><span class="section-title">Layanan</span><h2>Layanan Terpopuler</h2></div><a class="button small" href="services.php">Kelola</a></div>
    <div class="service-ranking">
    <?php $rank=0; foreach($topServices as $service): $rank++; ?>
        <div class="service-rank-row"><span class="service-rank-number">0<?= $rank ?></span><div><strong><?= htmlspecialchars($service['name']) ?></strong><small><?= (int)$service['total'] ?> booking</small></div><div class="service-rank-bar"><i style="width:<?= $topServices ? dashboard_percent((float)$service['total'],dashboard_max([(int)$topServices[0]['total']]),6) : 0 ?>%"></i></div></div>
    <?php endforeach; ?>
    <?php if(!$topServices): ?><div class="empty">Belum ada data layanan.</div><?php endif; ?>
    </div>
</section>
<section class="panel dashboard-chart-panel admin-status-panel">
  <div class="panel-heading"><div><span class="section-title">Status</span><h2>Distribusi Booking</h2></div><span class="chart-note">Realtime</span></div>
  <div class="status-chart-list">
  <?php foreach($statusItems as $si): $v=$statusValues[$si['key']]; ?>
    <div class="status-chart-row"><div><span><?=e($si['label'])?></span><strong><?=$v?></strong></div><div class="status-chart-track"><i class="<?=$si['class']?>" style="width:<?=dashboard_percent((float)$v,$maxStatus,5)?>%"></i></div></div>
  <?php endforeach; ?>
  </div>
</section>
</div>

<section class="panel">
<div class="panel-heading"><div><span class="section-title">Aktivitas</span><h2>Booking Terbaru</h2></div><a class="button small" href="bookings.php">Lihat semua</a></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Customer</th><th>Kendaraan</th><th>Layanan</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recent as $row): ?><tr><td>#<?= $row['id'] ?></td><td><?= htmlspecialchars($row['customer_name']) ?></td><td><?= htmlspecialchars($row['plate_number']) ?></td><td><?= htmlspecialchars($row['service_name']) ?></td><td><?= htmlspecialchars(dashboard_date_label($row['booking_date'])) ?></td><td><span class="badge badge-<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars(status_label($row['status'])) ?></span></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="6" class="empty">Belum ada booking.</td></tr><?php endif; ?></tbody></table></div>
</section>
<?php require '../includes/admin_footer.php'; ?>
