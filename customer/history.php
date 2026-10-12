<?php require_once '../includes/role.php';requireRole('customer');require_once '../config/database.php';require_once '../includes/helpers.php';$uid=$_SESSION['user_id'];$id=(int)($_GET['id']??0);$sql="SELECT b.*,v.plate_number,v.brand,v.model,v.year,v.color,s.name service_name,b.service_price,u.name mechanic_name,m.specialization,sr.inspection,sr.diagnosis,sr.action_taken,sr.notes,sr.completed_at,p.id payment_id,p.amount,p.method,p.status payment_status,p.paid_at FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id JOIN services s ON s.id=b.service_id LEFT JOIN mechanics m ON m.id=b.mechanic_id LEFT JOIN users u ON u.id=m.user_id LEFT JOIN service_records sr ON sr.booking_id=b.id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.user_id=?";$params=[$uid];if($id){$sql.=' AND b.id=?';$params[]=$id;}$sql.=' ORDER BY b.booking_date DESC';$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll();$pageTitle=$id?'Detail Servis':'Riwayat Servis';require '../includes/customer_header.php';?><?php if($id&&$rows):$x=$rows[0];$parts=$pdo->prepare('SELECT sp.*,p.name FROM service_parts sp LEFT JOIN spareparts p ON p.id=sp.sparepart_id JOIN service_records sr ON sr.id=sp.service_record_id WHERE sr.booking_id=?');$parts->execute([$x['id']]);$parts=$parts->fetchAll();?><section class="panel"><div class="panel-heading"><h2>Booking #<?=$x['id']?></h2><span class="badge badge-<?=$x['status']?>"><?=e(status_label($x['status']))?></span></div><div class="detail-grid"><div><strong>Kendaraan</strong><p><?=e($x['plate_number'].' · '.$x['brand'].' '.$x['model'])?></p></div><div><strong>Layanan</strong><p><?=e($x['service_name'])?></p></div><div><strong>Mekanik</strong><p><?=e($x['mechanic_name']?:'Belum ditugaskan')?></p></div><div><strong>Jadwal</strong><p><?=e(date('d-m-Y H:i',strtotime($x['booking_date'])))?></p></div></div>
<?php
$timelineSteps=[
    ['key'=>'booking','title'=>'Booking dibuat','desc'=>'Permintaan servis berhasil dibuat.'],
    ['key'=>'confirmed','title'=>'Booking dikonfirmasi','desc'=>'Jadwal dan layanan diproses oleh ServisKu.'],
    ['key'=>'service','title'=>'Sedang diservis','desc'=>'Kendaraan ditangani oleh mekanik.'],
    ['key'=>'completed','title'=>'Servis selesai','desc'=>'Pekerjaan servis telah selesai.'],
    ['key'=>'payment','title'=>'Pembayaran selesai','desc'=>'Pembayaran sudah dikonfirmasi.'],
];
$status=$x['status'];
$stepIndex=match($status){
    'pending'=>0,
    'confirmed','waiting'=>1,
    'in_progress'=>2,
    'waiting_payment'=>3,
    'paid'=>4,
    default=>-1,
};
?>
<div class="service-timeline">
    <div class="service-timeline-heading"><div><span class="section-title">Progres servis</span><h3>Status perjalanan booking</h3></div><span class="badge badge-<?=$x['status']?>"><?=e(status_label($x['status']))?></span></div>
    <?php if($status==='cancelled'): ?>
        <div class="timeline-cancelled"><strong>Booking dibatalkan</strong><span>Booking ini tidak lagi diproses.</span></div>
    <?php else: ?>
        <div class="timeline-track">
        <?php foreach($timelineSteps as $i=>$step): $done=$stepIndex>$i; $active=$stepIndex===$i; ?>
            <div class="timeline-step <?=$done?'done':''?> <?=$active?'active':''?>">
                <span class="timeline-dot"><?=$done?'✓':($i+1)?></span>
                <div><strong><?=e($step['title'])?></strong><small><?=e($step['desc'])?></small></div>
            </div>
            <?php if($i<count($timelineSteps)-1): ?><span class="timeline-line <?=$done?'done':''?>"></span><?php endif; ?>
        <?php endforeach; ?>
        </div>
        <?php if($x['payment_status']==='failed'): ?><div class="timeline-note danger"><strong>Pembayaran perlu dikirim ulang.</strong> <?=e($x['verification_note']?:'Bukti pembayaran sebelumnya ditolak.')?></div><?php endif; ?>
    <?php endif; ?>
</div><hr><h3>Pemeriksaan</h3><dl class="detail-list"><dt>Keluhan</dt><dd><?=nl2br(e($x['complaint']?:'-'))?></dd><dt>Inspeksi</dt><dd><?=nl2br(e($x['inspection']?:'-'))?></dd><dt>Diagnosis</dt><dd><?=nl2br(e($x['diagnosis']?:'-'))?></dd><dt>Tindakan</dt><dd><?=nl2br(e($x['action_taken']?:'-'))?></dd><dt>Catatan</dt><dd><?=nl2br(e($x['notes']?:'-'))?></dd></dl><h3>Sparepart</h3><table class="data-table"><tr><th>Nama</th><th>Qty</th><th>Harga</th></tr><?php foreach($parts as $p):?><tr><td><?=e($p['name'])?></td><td><?=$p['quantity']?></td><td><?=rupiah($p['price']*$p['quantity'])?></td></tr><?php endforeach;?><?php if(!$parts):?><tr><td colspan="3" class="empty">Tidak ada sparepart.</td></tr><?php endif;?></table><?php $partsTotal=array_sum(array_map(fn($p)=>(float)$p['price']*(int)$p['quantity'],$parts)); $grandTotal=(float)($x['amount']?:((float)$x['service_price']+$partsTotal)); ?><div class="invoice-summary"><div>Estimasi jasa <strong><?=rupiah($x['service_price'])?></strong></div><div>Total sparepart <strong><?=rupiah($partsTotal)?></strong></div><div>Total <strong><?=rupiah($grandTotal)?></strong></div></div><?php if($x['payment_status']!=='paid' && in_array($x['status'],['waiting_payment'],true)):?><a class="button" href="payment.php?id=<?=$x['id']?>">Lanjut Pembayaran</a> <a class="button secondary" href="invoice.php?id=<?=$x['id']?>">Lihat Invoice</a><?php elseif($x['payment_status']==='paid'):?><span class="success inline-message">Pembayaran sudah dikonfirmasi.</span> <a class="button secondary" href="invoice.php?id=<?=$x['id']?>">Lihat Invoice</a><?php endif;?></section><?php else:?><section class="panel"><div class="table-wrap"><table class="data-table"><tr><th>#</th><th>Tanggal</th><th>Kendaraan</th><th>Layanan</th><th>Status</th><th></th></tr><?php foreach($rows as $x):?><tr><td>#<?=$x['id']?></td><td><?=e(date('d-m-Y H:i',strtotime($x['booking_date'])))?></td><td><?=e($x['plate_number'])?></td><td><?=e($x['service_name'])?></td><td><span class="badge badge-<?=$x['status']?>"><?=e(status_label($x['status']))?></span></td><td><a class="button small" href="history.php?id=<?=$x['id']?>">Detail</a></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="6" class="empty">Belum ada riwayat.</td></tr><?php endif;?></table></div></section><?php endif;?><?php require '../includes/customer_footer.php';?>