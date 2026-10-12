<?php
require_once '../includes/role.php';
requireRole('mechanic');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/dashboard_helpers.php';

$mid = $pdo->prepare('SELECT id,status,specialization FROM mechanics WHERE user_id=?');
$mid->execute([$_SESSION['user_id']]);
$mechanic = $mid->fetch();

if (!$mechanic) {
    exit('Profil mekanik belum tersedia. Hubungi admin.');
}

$jobsQuery = $pdo->prepare("SELECT b.id,b.booking_date,b.status,b.complaint,v.plate_number,v.brand,v.model,s.name service_name,u.name customer_name,sr.id record_id
    FROM bookings b
    JOIN users u ON u.id=b.user_id
    JOIN vehicles v ON v.id=b.vehicle_id
    JOIN services s ON s.id=b.service_id
    LEFT JOIN service_records sr ON sr.booking_id=b.id
    WHERE b.mechanic_id=?
    ORDER BY FIELD(b.status,'in_progress','confirmed','waiting','pending','waiting_payment','paid','cancelled'), b.booking_date DESC");
$jobsQuery->execute([$mechanic['id']]);
$jobs = $jobsQuery->fetchAll();

$todayQuery = $pdo->prepare("SELECT b.id,b.booking_date,b.status,b.complaint,v.plate_number,v.brand,v.model,s.name service_name,u.name customer_name
    FROM bookings b
    JOIN users u ON u.id=b.user_id
    JOIN vehicles v ON v.id=b.vehicle_id
    JOIN services s ON s.id=b.service_id
    WHERE b.mechanic_id=? AND DATE(b.booking_date)=CURDATE()
    ORDER BY b.booking_date ASC");
$todayQuery->execute([$mechanic['id']]);
$todayJobs = $todayQuery->fetchAll();

$activeStatuses = ['confirmed', 'waiting', 'in_progress'];
$finishedStatuses = ['waiting_payment', 'paid'];
$activeJobs = array_values(array_filter($jobs, fn($x) => in_array($x['status'], $activeStatuses, true)));
$finishedJobs = array_values(array_filter($jobs, fn($x) => in_array($x['status'], $finishedStatuses, true)));
$inProgressJobs = array_values(array_filter($jobs, fn($x) => $x['status'] === 'in_progress'));
$waitingJobs = array_values(array_filter($jobs, fn($x) => in_array($x['status'], ['confirmed', 'waiting'], true)));

$pageTitle = 'Dashboard Mekanik';
require '../includes/mechanic_header.php';
?>
<div class="mechanic-dashboard-head">
    <div>
        <span class="muted">Workspace Mekanik</span>
        <h2>Pekerjaan Hari Ini</h2>
        <p><?= e($mechanic['specialization'] ?: 'Mekanik umum') ?> · <?= date('d F Y') ?></p>
    </div>
    <div class="mechanic-status-chip <?= e($mechanic['status']) ?>">
        <span></span><?= e(ucfirst($mechanic['status'] ?: 'available')) ?>
    </div>
</div>

<div class="stats-grid mechanic-stats">
    <div class="stat-card"><span>Total Pekerjaan</span><strong><?= count($jobs) ?></strong><small>Seluruh tugas yang pernah ditugaskan</small></div>
    <div class="stat-card"><span>Aktif</span><strong><?= count($activeJobs) ?></strong><small>Masih perlu ditangani</small></div>
    <div class="stat-card"><span>Sedang Dikerjakan</span><strong><?= count($inProgressJobs) ?></strong><small>Kendaraan sedang masuk proses</small></div>
    <div class="stat-card"><span>Selesai</span><strong><?= count($finishedJobs) ?></strong><small>Menunggu pembayaran atau sudah lunas</small></div>
</div>

<section class="mechanic-focus-grid">
    <div class="panel mechanic-today-panel">
        <div class="panel-heading">
            <div><span class="muted">Jadwal</span><h2>Pekerjaan Hari Ini</h2></div>
            <span class="mechanic-count-badge"><?= count($todayJobs) ?> tugas</span>
        </div>
        <?php if ($todayJobs): ?>
            <div class="mechanic-today-list">
                <?php foreach ($todayJobs as $job): ?>
                    <a class="mechanic-job-card" href="service.php?id=<?= (int)$job['id'] ?>">
                        <div class="mechanic-job-time"><?= e(dashboard_date_label($job['booking_date'], 'H:i')) ?></div>
                        <div class="mechanic-job-main">
                            <strong><?= e($job['service_name']) ?></strong>
                            <span><?= e($job['customer_name']) ?> · <?= e($job['plate_number']) ?></span>
                            <small><?= e($job['brand'].' '.$job['model']) ?></small>
                        </div>
                        <span class="badge badge-<?= e($job['status']) ?>"><?= e(status_label($job['status'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mechanic-empty-state"><strong>Tidak ada jadwal hari ini</strong><span>Belum ada booking yang ditugaskan kepada Anda untuk hari ini.</span></div>
        <?php endif; ?>
    </div>

    <div class="panel mechanic-progress-panel">
        <div class="panel-heading"><div><span class="muted">Ringkasan</span><h2>Status Pekerjaan</h2></div></div>
        <div class="mechanic-status-list">
            <div><span class="status-dot confirmed"></span><div><strong>Menunggu dikerjakan</strong><small><?= count($waitingJobs) ?> pekerjaan</small></div></div>
            <div><span class="status-dot in_progress"></span><div><strong>Sedang dikerjakan</strong><small><?= count($inProgressJobs) ?> pekerjaan</small></div></div>
            <div><span class="status-dot waiting_payment"></span><div><strong>Menunggu pembayaran</strong><small><?= count(array_filter($jobs, fn($x) => $x['status'] === 'waiting_payment')) ?> pekerjaan</small></div></div>
            <div><span class="status-dot paid"></span><div><strong>Selesai & lunas</strong><small><?= count(array_filter($jobs, fn($x) => $x['status'] === 'paid')) ?> pekerjaan</small></div></div>
        </div>
        <div class="mechanic-progress-note">
            <strong>Fokus saat ini</strong>
            <span><?= count($inProgressJobs) ? count($inProgressJobs).' kendaraan sedang dikerjakan.' : 'Belum ada kendaraan yang sedang dikerjakan.' ?></span>
        </div>
    </div>
</section>

<section class="panel dashboard-chart-panel mechanic-chart-panel">
    <div class="panel-heading"><div><span class="section-title">Analitik</span><h2>Distribusi Pekerjaan</h2></div><span class="chart-note"><?= count($jobs) ?> total</span></div>
    <div class="workload-chart">
      <?php $workload=[['label'=>'Menunggu','value'=>count($waitingJobs),'class'=>'waiting'],['label'=>'Dikerjakan','value'=>count($inProgressJobs),'class'=>'progress'],['label'=>'Menunggu Bayar','value'=>count(array_filter($jobs,fn($x)=>$x['status']==='waiting_payment')),'class'=>'payment'],['label'=>'Selesai','value'=>count(array_filter($jobs,fn($x)=>$x['status']==='paid')),'class'=>'done']]; $maxWork=dashboard_max(array_column($workload,'value')); foreach($workload as $w): ?>
      <div class="workload-row"><div class="workload-label"><span><?=e($w['label'])?></span><strong><?=$w['value']?></strong></div><div class="workload-track"><i class="<?=$w['class']?>" style="width:<?=dashboard_percent((float)$w['value'],$maxWork,4)?>%"></i></div></div>
      <?php endforeach; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div><span class="muted">Daftar tugas</span><h2>Pekerjaan Saya</h2></div>
        <span class="muted"><?= count($jobs) ?> total</span>
    </div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Booking</th><th>Customer</th><th>Kendaraan</th><th>Layanan</th><th>Jadwal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($jobs as $j): ?>
        <tr>
            <td>#<?= e($j['id']) ?></td>
            <td><?= e($j['customer_name']) ?><small><?= e($j['complaint'] ?: 'Tidak ada keluhan') ?></small></td>
            <td><?= e($j['plate_number']) ?><small><?= e($j['brand'].' '.$j['model']) ?></small></td>
            <td><?= e($j['service_name']) ?></td>
            <td><?= e(dashboard_date_label($j['booking_date'])) ?></td>
            <td><span class="badge badge-<?= e($j['status']) ?>"><?= e(status_label($j['status'])) ?></span></td>
            <td><a class="button small" href="service.php?id=<?= (int)$j['id'] ?>">Buka</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$jobs): ?><tr><td colspan="7" class="empty">Belum ada pekerjaan yang ditugaskan.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require '../includes/mechanic_footer.php'; ?>
