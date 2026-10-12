<?php
require_once '../includes/role.php';
requireRole('customer');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/dashboard_helpers.php';

$uid = $_SESSION['user_id'];
$v = $pdo->prepare('SELECT COUNT(*) FROM vehicles WHERE user_id=?');
$v->execute([$uid]);
$vehicleCount = $v->fetchColumn();
$b = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status NOT IN ('paid','cancelled')");
$b->execute([$uid]);
$active = $b->fetchColumn();
$h = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=?');
$h->execute([$uid]);
$history = $h->fetchColumn();
$latest = $pdo->prepare("SELECT b.*,v.plate_number,s.name service_name FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5");
$latest->execute([$uid]);
$latest = $latest->fetchAll();
$action = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status='waiting_payment'");
$action->execute([$uid]);
$waitingPayment = (int)$action->fetchColumn();
$paymentNotice = $waitingPayment > 0;
$monthlyCustomer = $pdo->prepare("SELECT MONTH(booking_date) month_number, COUNT(*) total FROM bookings WHERE user_id=? AND YEAR(booking_date)=YEAR(CURDATE()) GROUP BY MONTH(booking_date) ORDER BY MONTH(booking_date)");
$monthlyCustomer->execute([$uid]);
$customerMonthMap = [];
foreach ($monthlyCustomer->fetchAll() as $row) $customerMonthMap[(int)$row['month_number']] = (int)$row['total'];
$customerMonthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$customerMax = dashboard_max(array_values($customerMonthMap));
$pageTitle='Dashboard Customer';
require '../includes/customer_header.php';
?>
<section class="customer-welcome">
  <div>
    <span class="eyebrow">Customer workspace</span>
    <h2>Halo, <?=e($_SESSION['user_name'] ?? 'Customer')?> 👋</h2>
    <p>Pantau kendaraan, booking servis, dan riwayat perawatan Anda dari satu tempat.</p>
  </div>
  <a class="button" href="booking.php">+ Buat Booking</a>
</section>

<?php if ($paymentNotice): ?>
<section class="customer-attention">
  <div class="customer-attention-icon">Rp</div>
  <div><strong>Ada pembayaran yang perlu diselesaikan</strong><p><?= $waitingPayment ?> booking menunggu pembayaran. Buka riwayat untuk melanjutkan proses.</p></div>
  <a class="button small" href="history.php">Lihat Riwayat →</a>
</section>
<?php endif; ?>

<div class="stats-grid customer-stats">
  <div class="stat-card stat-vehicle"><span>Kendaraan Saya</span><strong><?=$vehicleCount?></strong><small>kendaraan terdaftar</small><a href="vehicles.php">Kelola kendaraan →</a></div>
  <div class="stat-card stat-active"><span>Booking Aktif</span><strong><?=$active?></strong><small>masih dalam proses</small><a href="history.php">Lihat status →</a></div>
  <div class="stat-card stat-history"><span>Total Booking</span><strong><?=$history?></strong><small>seluruh riwayat</small><a href="history.php">Lihat riwayat →</a></div>
</div>

<section class="customer-quick-grid">
  <a class="customer-quick-card" href="booking.php"><span class="quick-icon">＋</span><div><strong>Booking Servis</strong><p>Pilih kendaraan, layanan, tanggal, dan keluhan.</p></div><b>→</b></a>
  <a class="customer-quick-card" href="vehicles.php"><span class="quick-icon">🚗</span><div><strong>Kelola Kendaraan</strong><p>Pastikan data kendaraan Anda selalu terbaru.</p></div><b>→</b></a>
  <a class="customer-quick-card" href="history.php"><span class="quick-icon">◷</span><div><strong>Riwayat Servis</strong><p>Cek status servis, invoice, dan pembayaran.</p></div><b>→</b></a>
</section>

<section class="customer-journey">
  <div class="customer-journey-heading"><span class="section-title">Alur servis</span><strong>Dari booking sampai selesai</strong></div>
  <div class="customer-journey-steps">
    <div><span>1</span><strong>Booking</strong><small>Pilih layanan</small></div>
    <i>→</i>
    <div><span>2</span><strong>Konfirmasi</strong><small>Jadwal diproses</small></div>
    <i>→</i>
    <div><span>3</span><strong>Servis</strong><small>Ditangani mekanik</small></div>
    <i>→</i>
    <div><span>4</span><strong>Selesai</strong><small>Bayar &amp; simpan riwayat</small></div>
  </div>
</section>

<section class="panel dashboard-chart-panel customer-chart-panel">
  <div class="panel-heading"><div><span class="section-title">Analitik</span><h2>Aktivitas Booking Tahun Ini</h2></div><span class="chart-note"><?= date('Y') ?></span></div>
  <div class="mini-bars" aria-label="Grafik booking customer tahun ini">
  <?php for($m=1;$m<=12;$m++): $v=$customerMonthMap[$m]??0; $h=$v?dashboard_percent((float)$v,$customerMax,8):4; ?>
    <div class="mini-bar-item"><span class="mini-bar-value"><?= $v ?></span><div class="mini-bar-track"><i style="height:<?= $h ?>%"></i></div><small><?= $customerMonthNames[$m] ?></small></div>
  <?php endfor; ?>
  </div>
</section>

<section class="panel customer-booking-panel">
  <div class="panel-heading">
    <div>
      <span class="section-title">Aktivitas</span>
      <h2>Booking Terbaru</h2>
    </div>
    <a class="button small secondary" href="history.php">Lihat Semua</a>
  </div>

  <?php if ($latest): ?>

    <!-- Tampilan tabel untuk laptop -->
    <div class="table-wrap customer-booking-desktop">
      <table class="data-table">
        <tr>
          <th>Tanggal</th>
          <th>Kendaraan</th>
          <th>Layanan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>

        <?php foreach ($latest as $x): ?>
        <tr>
          <td><?= e(dashboard_date_label($x['booking_date'])) ?></td>
          <td><strong><?= e($x['plate_number']) ?></strong></td>
          <td><?= e($x['service_name']) ?></td>
          <td>
            <span class="badge badge-<?= e($x['status']) ?>">
              <?= e(status_label($x['status'])) ?>
            </span>
          </td>
          <td>
            <a class="button small secondary"
               href="history.php?id=<?= (int)$x['id'] ?>">
              Detail
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <!-- Tampilan kartu untuk HP -->
    <div class="customer-booking-mobile">
      <?php foreach ($latest as $x): ?>
        <article class="customer-booking-card">
          <div class="customer-booking-card-top">
            <strong><?= e($x['plate_number']) ?></strong>
            <span class="badge badge-<?= e($x['status']) ?>">
              <?= e(status_label($x['status'])) ?>
            </span>
          </div>

          <div class="customer-booking-card-info">
            <div>
              <small>Tanggal booking</small>
              <span><?= e(dashboard_date_label($x['booking_date'])) ?></span>
            </div>
            <div>
              <small>Layanan</small>
              <span><?= e($x['service_name']) ?></span>
            </div>
          </div>

          <a class="button small secondary customer-booking-detail"
             href="history.php?id=<?= (int)$x['id'] ?>">
            Lihat Detail →
          </a>
        </article>
      <?php endforeach; ?>
    </div>

  <?php else: ?>
    <div class="customer-empty">
      <span>📅</span>
      <strong>Belum ada booking</strong>
      <p>Mulai dengan membuat booking servis pertama Anda.</p>
      <a class="button small" href="booking.php">Buat Booking</a>
    </div>
  <?php endif; ?>
</section>
<?php require '../includes/customer_footer.php';?>
