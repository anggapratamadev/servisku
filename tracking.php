<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.id,b.booking_date,b.status,b.complaint,b.created_at,v.plate_number,v.brand,v.model,s.name service_name,m.status mechanic_status,mu.name mechanic_name FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id LEFT JOIN mechanics m ON m.id=b.mechanic_id LEFT JOIN users mu ON mu.id=m.user_id WHERE b.id=?");
$stmt->execute([$id]);
$booking = $stmt->fetch();
if (!$booking) { http_response_code(404); exit('Booking tidak ditemukan.'); }
$pageTitle = 'Tracking Booking #'.str_pad($id,6,'0',STR_PAD_LEFT);
$status = $booking['status'];
$steps = [
    ['key'=>'booking','title'=>'Booking dibuat'],
    ['key'=>'confirmed','title'=>'Dikonfirmasi'],
    ['key'=>'service','title'=>'Sedang diservis'],
    ['key'=>'completed','title'=>'Servis selesai'],
    ['key'=>'payment','title'=>'Pembayaran selesai'],
];
$index = match($status){ 'pending'=>0,'confirmed','waiting'=>1,'in_progress'=>2,'waiting_payment'=>3,'paid'=>4, default=>-1 };
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> - ServisKu</title>
<link rel="stylesheet" href="assets/css/style.css?v=41">
</head>
<body class="tracking-page">
<main class="tracking-shell">
  <div class="tracking-brand"><span class="brand-mark">SK</span><div><strong>ServisKu</strong><small>Vehicle Service Management</small></div></div>
  <section class="panel tracking-card">
    <div class="tracking-head"><div><span class="muted">QR SERVICE TRACKING</span><h1>Booking #<?=str_pad($id,6,'0',STR_PAD_LEFT)?></h1></div><span class="badge badge-<?=e($status)?>"><?=e(status_label($status))?></span></div>
    <?php if($status==='cancelled'): ?>
      <div class="timeline-cancelled"><strong>Booking dibatalkan</strong><span>Booking ini tidak lagi diproses.</span></div>
    <?php else: ?>
      <div class="tracking-steps">
      <?php foreach($steps as $i=>$step): $done=$index>$i; $active=$index===$i; ?>
        <div class="tracking-step <?=$done?'done':''?> <?=$active?'active':''?>"><span><?=$done?'✓':($i+1)?></span><strong><?=e($step['title'])?></strong></div>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="tracking-info-grid">
      <div><span>Kendaraan</span><strong><?=e($booking['plate_number'])?></strong><small><?=e($booking['brand'].' '.$booking['model'])?></small></div>
      <div><span>Layanan</span><strong><?=e($booking['service_name'])?></strong><small>Jadwal <?=e(date('d-m-Y H:i',strtotime($booking['booking_date'])))?></small></div>
      <div><span>Mekanik</span><strong><?=e($booking['mechanic_name']?:'Belum ditugaskan')?></strong><small><?=e($booking['mechanic_status']?:'Menunggu penugasan')?></small></div>
    </div>
    <div class="tracking-note"><strong>Catatan servis</strong><p><?=nl2br(e($booking['complaint']?:'Tidak ada keluhan tambahan.'))?></p></div>
    <p class="tracking-disclaimer">Halaman ini menampilkan informasi ringkas untuk tracking booking. Detail invoice dan data pembayaran tetap tersedia setelah login ke akun ServisKu.</p>
  </section>
</main>
</body>
</html>
