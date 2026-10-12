<?php
require_once '../includes/role.php';
requireRole('customer');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

// One-time submission token prevents duplicate bookings caused by double-click or browser resubmission.
if (empty($_SESSION['booking_form_token'])) {
    $_SESSION['booking_form_token'] = bin2hex(random_bytes(24));
}

$error = '';
$success = '';

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $success = 'Booking berhasil dibuat dan menunggu konfirmasi admin.';
}

$vehicleStmt = $pdo->prepare('SELECT * FROM vehicles WHERE user_id = ? ORDER BY id DESC');
$vehicleStmt->execute([$_SESSION['user_id']]);
$vehicles = $vehicleStmt->fetchAll();

$services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $submittedToken = (string)($_POST['booking_form_token'] ?? '');
    $sessionToken = (string)($_SESSION['booking_form_token'] ?? '');
    if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        $error = 'Form booking sudah diproses. Silakan gunakan formulir booking yang baru.';
    } else {
    // Consume the token before the insert. PHP session locking also serializes
    // simultaneous requests from the same browser session.
    unset($_SESSION['booking_form_token']);

    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $complaint = trim($_POST['complaint'] ?? '');

    $check = $pdo->prepare('SELECT id FROM vehicles WHERE id = ? AND user_id = ?');
    $check->execute([$vehicleId, $_SESSION['user_id']]);
    $vehicleExists = $check->fetchColumn();

    $checkService = $pdo->prepare("SELECT id FROM services WHERE id = ? AND status = 'active'");
    $checkService->execute([$serviceId]);
    $serviceExists = $checkService->fetchColumn();

    $servicePriceStmt = $pdo->prepare("SELECT price FROM services WHERE id = ? AND status = 'active'");
    $servicePriceStmt->execute([$serviceId]);
    $servicePrice = $servicePriceStmt->fetchColumn();

    if ($error !== '') {
        // Token validation failed; do not process the booking.
    } elseif (!$vehicleExists || !$serviceExists || $servicePrice === false || $bookingDate === '') {
        $error = 'Data booking tidak valid.';
    } elseif (strtotime($bookingDate) < time()) {
        $error = 'Tanggal booking harus di masa mendatang.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO bookings (user_id, vehicle_id, service_id, service_price, booking_date, complaint, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([
            $_SESSION['user_id'], $vehicleId, $serviceId, $servicePrice, $bookingDate,
            $complaint !== '' ? $complaint : null
        ]);
        $newBookingId = (int)$pdo->lastInsertId();
        log_activity($pdo, 'booking_created', 'Customer membuat booking servis baru.', 'booking', $newBookingId);
        notify_role($pdo, 'admin', 'Booking baru', 'Ada booking servis baru yang menunggu konfirmasi admin.', 'booking', 'booking', $newBookingId);
        // Post/Redirect/Get prevents refresh from submitting the booking again.
        $_SESSION['booking_form_token'] = bin2hex(random_bytes(24));
        header('Location: booking.php?created=1');
        exit;
    }

    // Invalid submission: issue a fresh token so the customer can retry.
    if (empty($_SESSION['booking_form_token'])) {
        $_SESSION['booking_form_token'] = bin2hex(random_bytes(24));
    }
    }
}

$bookings = $pdo->prepare(
    "SELECT b.*, v.plate_number, v.brand, v.model, s.name AS service_name
     FROM bookings b
     JOIN vehicles v ON v.id = b.vehicle_id
     JOIN services s ON s.id = b.service_id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC"
);
$bookings->execute([$_SESSION['user_id']]);
$myBookings = $bookings->fetchAll();
?>
<?php $pageTitle='Booking Servis'; require '../includes/customer_header.php'; ?>
<?php if ($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
<?php if ($success): ?><div class="success"><?=e($success)?></div><?php endif; ?>
<section class="panel">
  <div class="panel-heading"><div><span class="section-title">Reservasi</span><h2>Booking servis baru</h2></div><a class="button secondary small" href="vehicles.php">Kelola kendaraan</a></div>
  <?php if (!$vehicles): ?><p>Tambahkan kendaraan terlebih dahulu agar bisa membuat booking.</p><a class="button" href="vehicles.php">+ Tambah kendaraan</a>
  <?php elseif (!$services): ?><div class="alert">Belum ada layanan aktif. Silakan hubungi admin.</div>
  <?php else: ?><form method="POST" class="compact-form" id="booking-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="booking_form_token" value="<?=e($_SESSION['booking_form_token'])?>">
    <label>Kendaraan<select name="vehicle_id" required><option value="">-- Pilih kendaraan --</option><?php foreach($vehicles as $v): ?><option value="<?=$v['id']?>"><?=e($v['plate_number'].' · '.$v['brand'].' '.$v['model'])?></option><?php endforeach; ?></select></label>
    <label>Layanan<select name="service_id" required><option value="">-- Pilih layanan --</option><?php foreach($services as $sv): ?><option value="<?=$sv['id']?>"><?=e($sv['name'].' · '.rupiah($sv['price']).' · '.$sv['duration'].' menit')?></option><?php endforeach; ?></select></label>
    <label>Tanggal & Jam<input type="datetime-local" name="booking_date" min="<?=date('Y-m-d\TH:i')?>" required></label>
    <label>Keluhan<textarea name="complaint" rows="4" placeholder="Contoh: mesin bergetar saat idle..."></textarea></label>
    <button type="submit" id="booking-submit">Buat Booking</button>
  </form><?php endif; ?>
</section>
<section class="panel"><div class="panel-heading"><div><span class="section-title">Aktivitas</span><h2>Booking saya</h2></div><a href="history.php">Lihat riwayat</a></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Tanggal</th><th>Kendaraan</th><th>Layanan</th><th>Status</th><th>Keluhan</th></tr></thead><tbody>
<?php foreach($myBookings as $b): ?><tr><td><?=e(date('d-m-Y H:i',strtotime($b['booking_date'])))?></td><td><strong><?=e($b['plate_number'])?></strong><small><?=e($b['brand'].' '.$b['model'])?></small></td><td><?=e($b['service_name'])?></td><td><span class="badge badge-<?=e($b['status'])?>"><?=e(status_label($b['status']))?></span></td><td><?=e($b['complaint']?:'-')?></td></tr><?php endforeach; ?><?php if(!$myBookings): ?><tr><td colspan="5" class="empty">Belum ada booking.</td></tr><?php endif; ?></tbody></table></div></section>
<script>
const bookingForm=document.getElementById('booking-form');
if(bookingForm){bookingForm.addEventListener('submit',function(){const btn=document.getElementById('booking-submit');if(btn){btn.disabled=true;btn.textContent='Memproses...';}});}
</script>
<?php require '../includes/customer_footer.php'; ?>