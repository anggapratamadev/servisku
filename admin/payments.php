<?php
require_once '../includes/role.php'; requireRole('admin');
require_once '../config/database.php'; require_once '../includes/helpers.php'; require_once '../includes/csrf.php';
$error='';$success='';
$q=trim($_GET['q']??''); $filterStatus=$_GET['status']??'';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf(); $id=(int)($_POST['payment_id']??0); $action=$_POST['action']??''; $note=trim($_POST['note']??'');
    try{
        if($id<1) throw new Exception('Pembayaran tidak valid.');
        $pdo->beginTransaction();
        $s=$pdo->prepare('SELECT id,booking_id,status FROM payments WHERE id=? FOR UPDATE'); $s->execute([$id]); $p=$s->fetch();
        if(!$p) throw new Exception('Pembayaran tidak ditemukan.');
        if($p['status']==='paid') throw new Exception('Pembayaran sudah lunas.');
        if($action==='approve'){
            $s=$pdo->prepare("UPDATE payments SET status='paid',paid_at=NOW(),verified_by=?,verified_at=NOW(),verification_note=? WHERE id=?"); $s->execute([$_SESSION['user_id'],$note?:'Bukti pembayaran diverifikasi admin.',$id]);
            $s=$pdo->prepare("UPDATE bookings SET status='paid' WHERE id=? AND status='waiting_payment'"); $s->execute([$p['booking_id']]);
            $n=$pdo->prepare('SELECT user_id FROM bookings WHERE id=?');$n->execute([(int)$p['booking_id']]);$customerUserId=(int)$n->fetchColumn();notify_user($pdo,$customerUserId,'Pembayaran disetujui','Pembayaran Anda telah diverifikasi dan booking dinyatakan lunas.','success','booking',(int)$p['booking_id']);
            log_activity($pdo, 'payment_approved', 'Admin menyetujui pembayaran dan booking menjadi lunas.', 'payment', $id);
            if($s->rowCount()!==1) throw new Exception('Booking tidak berada pada status menunggu pembayaran.');
            $success='Pembayaran disetujui dan booking dinyatakan lunas.';
        } elseif($action==='reject'){
            if($note==='') throw new Exception('Berikan alasan penolakan bukti pembayaran.');
            $s=$pdo->prepare("UPDATE payments SET status='failed',verified_by=?,verified_at=NOW(),verification_note=? WHERE id=?"); $s->execute([$_SESSION['user_id'],$note,$id]);
            log_activity($pdo, 'payment_rejected', 'Admin menolak bukti pembayaran: '.$note, 'payment', $id);
                        $n=$pdo->prepare('SELECT user_id FROM bookings WHERE id=?');$n->execute([(int)$p['booking_id']]);$customerUserId=(int)$n->fetchColumn();notify_user($pdo,$customerUserId,'Pembayaran ditolak','Pembayaran Anda ditolak oleh admin. Silakan cek catatan verifikasi dan kirim ulang bila diperlukan.','danger','booking',(int)$p['booking_id']);
$success='Bukti pembayaran ditolak. Customer dapat mengirim ulang bukti.';
        } else throw new Exception('Aksi tidak valid.');
        $pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=servisku_error_message($e);}
}
$rows=$pdo->query("SELECT p.*,b.booking_date,b.status booking_status,u.name customer_name,v.plate_number,mu.name verifier_name FROM payments p JOIN bookings b ON b.id=p.booking_id JOIN users u ON u.id=b.user_id JOIN vehicles v ON v.id=b.vehicle_id LEFT JOIN users mu ON mu.id=p.verified_by ORDER BY (p.status='pending') DESC,p.created_at DESC")->fetchAll();
$pageTitle='Pembayaran'; require '../includes/admin_header.php';
?>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?><?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<section class="panel"><div class="panel-heading"><div><span class="muted">Verifikasi transaksi</span><h2>Pembayaran</h2></div><span class="muted"><?=count($rows)?> transaksi</span></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Invoice</th><th>Customer</th><th>Kendaraan</th><th>Jumlah</th><th>Metode</th><th>Bukti</th><th>Status</th><th>Verifikasi</th></tr></thead><tbody>
<?php foreach($rows as $x):?><tr><td><a class="invoice-link" href="../customer/invoice.php?id=<?=$x['booking_id']?>" target="_blank"><strong>#INV-<?=str_pad($x['booking_id'],6,'0',STR_PAD_LEFT)?></strong></a><small><?=e(date('d-m-Y H:i',strtotime($x['booking_date'])))?></small></td><td><?=e($x['customer_name'])?></td><td><?=e($x['plate_number'])?></td><td><?=rupiah($x['amount'])?></td><td><?=e(strtoupper($x['method']))?></td><td><?php if($x['proof_file']):?><a class="button small" target="_blank" href="payment_proof.php?id=<?=$x['id']?>">Lihat Bukti</a><?php else:?><span class="muted">Tidak ada</span><?php endif;?></td><td><span class="badge badge-<?=$x['status']?>"><?=e($x['status']==='pending'?'Menunggu Verifikasi':payment_status_label($x['status']))?></span></td><td><?php if($x['status']==='pending'):?><div class="verify-actions"><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="payment_id" value="<?=$x['id']?>"><input type="hidden" name="action" value="approve"><button class="small" type="submit">Setujui</button></form><form method="post" class="reject-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="payment_id" value="<?=$x['id']?>"><input type="hidden" name="action" value="reject"><input name="note" placeholder="Alasan penolakan" required><button class="small danger" type="submit">Tolak</button></form></div><?php else:?><?=e($x['verifier_name']?:'-')?><?php endif;?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="8" class="empty">Belum ada transaksi.</td></tr><?php endif;?></tbody></table></div></section>
<?php require '../includes/admin_footer.php';?>
