<?php
require_once '../includes/role.php'; requireRole('customer');
require_once '../config/database.php'; require_once '../includes/helpers.php';
$uid=(int)$_SESSION['user_id']; $id=(int)($_GET['id']??0);
$q=$pdo->prepare("SELECT b.*,u.name customer_name,u.email customer_email,u.phone customer_phone,v.plate_number,v.brand,v.model,v.year,s.name service_name,s.description service_description,muser.name mechanic_name,p.id payment_id,p.amount payment_amount,p.method payment_method,p.status payment_status,p.paid_at,p.verification_note FROM bookings b JOIN users u ON u.id=b.user_id JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id LEFT JOIN mechanics m ON m.id=b.mechanic_id LEFT JOIN users muser ON muser.id=m.user_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.id=? AND b.user_id=?");
$q->execute([$id,$uid]); $b=$q->fetch();
if(!$b) exit('Invoice tidak ditemukan.');
$partsQ=$pdo->prepare('SELECT sp.quantity,sp.price,spare.name FROM service_parts sp JOIN service_records sr ON sr.id=sp.service_record_id LEFT JOIN spareparts spare ON spare.id=sp.sparepart_id WHERE sr.booking_id=?');
$partsQ->execute([$id]); $parts=$partsQ->fetchAll();
$partsTotal=0; foreach($parts as $p){$partsTotal+=(float)$p['price']*(int)$p['quantity'];}
$total=(float)($b['payment_amount'] ?: ((float)$b['service_price']+$partsTotal));
$qrTarget=app_base_url().'/tracking.php?id='.$id;
$qrImage='https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=12&data='.rawurlencode($qrTarget);
$pageTitle='Invoice #'.str_pad($id,6,'0',STR_PAD_LEFT); require '../includes/customer_header.php';
?>
<div class="invoice-toolbar no-print"><a class="button secondary" href="history.php?id=<?=$id?>">← Kembali</a><button type="button" onclick="window.print()">Cetak Invoice</button></div>
<section class="invoice-sheet">
  <header class="invoice-head"><div><span class="invoice-brand">ServisKu</span><h1>INVOICE</h1><p>#INV-<?=str_pad($id,6,'0',STR_PAD_LEFT)?></p></div><div class="invoice-meta"><strong><?=e(status_label($b['status']))?></strong><span><?=e(date('d M Y H:i',strtotime($b['booking_date'])))?></span></div></header>
  <div class="invoice-parties"><div><span>DITAGIHKAN KEPADA</span><strong><?=e($b['customer_name'])?></strong><p><?=e($b['customer_email'])?><br><?=e($b['customer_phone']?:'-')?></p></div><div><span>KENDARAAN</span><strong><?=e($b['plate_number'])?></strong><p><?=e($b['brand'].' '.$b['model'])?><br>Tahun <?=e($b['year'])?></p></div><div><span>MEKANIK</span><strong><?=e($b['mechanic_name']?:'Belum ditugaskan')?></strong><p><?=e($b['service_name'])?></p></div></div>
  <div class="invoice-table-wrap"><table class="invoice-table"><thead><tr><th>Deskripsi</th><th>Qty</th><th>Harga</th><th>Total</th></tr></thead><tbody><tr><td><strong><?=e($b['service_name'])?></strong><small><?=e($b['service_description']?:'Jasa servis kendaraan')?></small></td><td>1</td><td><?=rupiah($b['service_price'])?></td><td><?=rupiah($b['service_price'])?></td></tr><?php foreach($parts as $p):?><tr><td><?=e($p['name']?:'Sparepart')?></td><td><?=$p['quantity']?></td><td><?=rupiah($p['price'])?></td><td><?=rupiah((float)$p['price']*(int)$p['quantity'])?></td></tr><?php endforeach;?></tbody></table></div>
  <div class="invoice-bottom"><div class="invoice-qr"><span class="section-title">QR booking</span><strong>Scan untuk tracking servis</strong><img src="<?=e($qrImage)?>" alt="QR tracking booking #<?=str_pad($id,6,'0',STR_PAD_LEFT)?>"><small><?=e($qrTarget)?></small></div><div class="invoice-note"><strong>Catatan</strong><p><?=nl2br(e($b['complaint']?:'Tidak ada catatan tambahan.'))?></p><?php if($b['verification_note']):?><p><strong>Catatan verifikasi:</strong> <?=e($b['verification_note'])?></p><?php endif;?></div><div class="invoice-totals"><div><span>Jasa servis</span><strong><?=rupiah($b['service_price'])?></strong></div><div><span>Sparepart</span><strong><?=rupiah($partsTotal)?></strong></div><div class="grand"><span>Total</span><strong><?=rupiah($total)?></strong></div></div></div>
  <footer class="invoice-foot"><span>Terima kasih telah menggunakan ServisKu.</span><span><?php if($b['payment_status']==='paid'):?>Pembayaran: Lunas · <?=e(date('d M Y H:i',strtotime($b['paid_at'])))?><?php elseif($b['payment_status']==='pending'):?>Pembayaran: Menunggu verifikasi<?php elseif($b['payment_status']==='failed'):?>Pembayaran: Perlu dikirim ulang<?php else:?>Pembayaran: Belum diproses<?php endif;?></span></footer>
</section>
<?php require '../includes/customer_footer.php'; ?>
