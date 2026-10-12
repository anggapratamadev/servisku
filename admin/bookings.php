<?php
require_once '../includes/role.php'; requireRole('admin'); require_once '../config/database.php'; require_once '../includes/csrf.php'; require_once '../includes/helpers.php';
$allowed=['pending','confirmed','waiting','in_progress','waiting_payment','paid','cancelled'];$error='';$success='';
$q=trim($_GET['q']??''); $filterStatus=$_GET['status']??''; $filterDate=$_GET['date']??'';
$transitions=[
 'pending'=>['confirmed','cancelled'],
 'confirmed'=>['waiting','cancelled'],
 'waiting'=>['confirmed','cancelled'],
 'in_progress'=>[],
 'waiting_payment'=>[],
 'paid'=>[],
 'cancelled'=>[]
];
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
 $bookingId=(int)($_POST['booking_id']??0);$action=$_POST['action']??'';
 if($bookingId<1)$error='Booking tidak valid.';
 elseif($action==='status'){
  $status=$_POST['status']??'';
  try{
   $pdo->beginTransaction();
   $s=$pdo->prepare('SELECT mechanic_id,status FROM bookings WHERE id=? FOR UPDATE');$s->execute([$bookingId]);$current=$s->fetch();
   if(!$current) throw new Exception('Booking tidak ditemukan.');
   if(!isset($transitions[$current['status']]) || !in_array($status,$transitions[$current['status']],true)) throw new Exception('Perubahan status tidak diizinkan. Gunakan alur servis sesuai tahapnya.');
   $s=$pdo->prepare('UPDATE bookings SET status=? WHERE id=?');$s->execute([$status,$bookingId]);
   log_activity($pdo, 'booking_status_changed', 'Admin mengubah status booking dari '.status_label($current['status']).' menjadi '.status_label($status).'.', 'booking', $bookingId);
   $n=$pdo->prepare('SELECT user_id FROM bookings WHERE id=?');$n->execute([$bookingId]);$customerUserId=(int)$n->fetchColumn();notify_user($pdo,$customerUserId,'Status booking diperbarui','Status booking Anda telah diperbarui oleh admin menjadi '.status_label($status).'.','booking','booking',$bookingId);
   if($status==='cancelled' && $current['mechanic_id']){
    $s=$pdo->prepare("UPDATE mechanics SET status='available' WHERE id=? AND status='busy'");$s->execute([(int)$current['mechanic_id']]);
   }
   $pdo->commit();$success='Status booking diperbarui.';
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=servisku_error_message($e);}
 }
 elseif($action==='assign'){
  $mechanicId=(int)($_POST['mechanic_id']??0);if($mechanicId<1)$error='Pilih mekanik.';else{try{$pdo->beginTransaction();$s=$pdo->prepare('SELECT mechanic_id,status FROM bookings WHERE id=? FOR UPDATE');$s->execute([$bookingId]);$current=$s->fetch();if(!$current)throw new Exception('Booking tidak ditemukan.');if(!in_array($current['status'],['pending','confirmed','waiting'],true))throw new Exception('Mekanik hanya dapat ditugaskan pada booking yang belum masuk tahap pengerjaan/selesai.');$currentMechanic=(int)($current['mechanic_id']??0);if($currentMechanic===$mechanicId){$pdo->commit();$success='Mekanik tersebut sudah ditugaskan.';}else{$s=$pdo->prepare("SELECT id FROM mechanics WHERE id=? AND status='available'");$s->execute([$mechanicId]);if(!$s->fetchColumn())throw new Exception('Mekanik tidak tersedia. Pilih mekanik berstatus available.');if($currentMechanic){$s=$pdo->prepare("UPDATE mechanics SET status='available' WHERE id=? AND status='busy'");$s->execute([$currentMechanic]);}$s=$pdo->prepare("UPDATE bookings SET mechanic_id=?, status=IF(status='pending','confirmed',status) WHERE id=?");$s->execute([$mechanicId,$bookingId]);$s=$pdo->prepare("UPDATE mechanics SET status='busy' WHERE id=?");$s->execute([$mechanicId]);
   log_activity($pdo, 'mechanic_assigned', 'Admin menugaskan mekanik ke booking.', 'booking', $bookingId);
   $n=$pdo->prepare('SELECT u.id user_id FROM mechanics m JOIN users u ON u.id=m.user_id WHERE m.id=?');$n->execute([$mechanicId]);$mechanicUserId=(int)$n->fetchColumn();notify_user($pdo,$mechanicUserId,'Pekerjaan baru ditugaskan','Anda mendapat penugasan servis baru. Silakan buka daftar pekerjaan.','booking','booking',$bookingId);
   $pdo->commit();$success='Mekanik berhasil ditugaskan.';}}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=servisku_error_message($e);}}
 }
}
$mechanics=$pdo->query("SELECT m.id,u.name,m.specialization,m.status FROM mechanics m JOIN users u ON u.id=m.user_id WHERE m.status <> 'inactive' ORDER BY m.status='available' DESC,u.name")->fetchAll();
$sql="SELECT b.*,u.name customer_name,v.plate_number,v.brand,v.model,s.name service_name,m.id assigned_mechanic_id,mu.name mechanic_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id LEFT JOIN mechanics m ON m.id=b.mechanic_id LEFT JOIN users mu ON mu.id=m.user_id WHERE 1=1"; $params=[]; if($q!==''){ $sql.=" AND (u.name LIKE ? OR v.plate_number LIKE ? OR v.brand LIKE ? OR v.model LIKE ? OR s.name LIKE ? OR CAST(b.id AS CHAR) LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like,$like,$like,$like]; } if(in_array($filterStatus,$allowed,true)){ $sql.=" AND b.status=?"; $params[]=$filterStatus; } if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$filterDate)){ $sql.=" AND DATE(b.booking_date)=?"; $params[]=$filterDate; } $sql.=" ORDER BY b.booking_date DESC"; $stmt=$pdo->prepare($sql); $stmt->execute($params); $bookings=$stmt->fetchAll();
$pageTitle='Data Booking'; require '../includes/admin_header.php';
?>
<?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<section class="panel"><div class="panel-heading"><h2>Booking Servis</h2><span class="muted"><?= count($bookings) ?> hasil</span></div><form method="get" class="admin-filter-form admin-filter-booking"><input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari customer, plat, kendaraan, layanan..."><select name="status"><option value="">Semua status</option><?php foreach($allowed as $st): ?><option value="<?= $st ?>" <?= $filterStatus===$st?'selected':'' ?>><?= htmlspecialchars(status_label($st)) ?></option><?php endforeach; ?></select><input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>"><button class="small" type="submit">Filter</button><a class="button small secondary" href="bookings.php">Reset</a></form><div class="table-wrap"><table class="data-table"><thead><tr><th>Customer</th><th>Kendaraan</th><th>Layanan</th><th>Tanggal</th><th>Mekanik</th><th>Status</th><th>QR</th></tr></thead><tbody>
<?php foreach($bookings as $b): ?><tr><td><strong><?= htmlspecialchars($b['customer_name']) ?></strong><small><?= htmlspecialchars($b['complaint']?:'Tidak ada keluhan') ?></small></td><td><?= htmlspecialchars($b['plate_number'].' - '.$b['brand'].' '.$b['model']) ?></td><td><?= htmlspecialchars($b['service_name']) ?></td><td><?= htmlspecialchars(date('d-m-Y H:i',strtotime($b['booking_date']))) ?></td><td><form method="post" class="compact-inline"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="action" value="assign"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><select name="mechanic_id"><option value="">-- Pilih mekanik --</option><?php foreach($mechanics as $m): ?><option value="<?= $m['id'] ?>" <?= (int)$b['assigned_mechanic_id']===(int)$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name'].' · '.$m['status']) ?></option><?php endforeach; ?></select><button class="small" type="submit">Assign</button></form></td><td><form method="post" class="compact-inline"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="action" value="status"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><select name="status"><?php $statusOptions=array_unique(array_merge([$b['status']],$transitions[$b['status']]??[])); foreach($statusOptions as $st): ?><option value="<?= $st ?>" <?= $st===$b['status']?'selected':'' ?>><?= htmlspecialchars(status_label($st)) ?></option><?php endforeach; ?></select><button class="small" type="submit">Update</button></form></td><td><a class="button small secondary" target="_blank" href="../tracking.php?id=<?= $b['id'] ?>">QR / Tracking</a></td></tr><?php endforeach; ?>
<?php if(!$bookings): ?><tr><td colspan="7" class="empty">Belum ada booking.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require '../includes/admin_footer.php'; ?>
