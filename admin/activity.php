<?php
require_once '../includes/role.php'; requireRole('admin');
require_once '../config/database.php'; require_once '../includes/helpers.php';
$q=trim($_GET['q']??''); $action=trim($_GET['action']??''); $date=trim($_GET['date']??'');
$actions=$pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
$sql="SELECT a.*,u.name user_name,u.role user_role FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id WHERE 1=1"; $params=[];
if($q!==''){ $sql.=" AND (a.description LIKE ? OR a.entity_type LIKE ? OR u.name LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like]; }
if($action!==''){ $sql.=" AND a.action=?"; $params[]=$action; }
if(preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$date)){ $sql.=" AND DATE(a.created_at)=?"; $params[]=$date; }
$sql.=" ORDER BY a.created_at DESC LIMIT 200"; $stmt=$pdo->prepare($sql); $stmt->execute($params); $logs=$stmt->fetchAll();
$actionLabels=[
 'booking_created'=>'Booking dibuat','booking_status_changed'=>'Status booking berubah','mechanic_assigned'=>'Mekanik ditugaskan',
 'service_note_saved'=>'Catatan servis disimpan','sparepart_added'=>'Sparepart ditambahkan','sparepart_removed'=>'Sparepart dihapus',
 'service_completed'=>'Servis selesai','payment_submitted'=>'Pembayaran dikirim','payment_approved'=>'Pembayaran disetujui','payment_rejected'=>'Pembayaran ditolak'
];
$pageTitle='Activity Log'; require '../includes/admin_header.php';
?>
<section class="panel">
 <div class="panel-heading"><div><span class="muted">Audit operasional</span><h2>Activity Log</h2></div><span class="muted"><?=count($logs)?> aktivitas terakhir</span></div>
 <form method="get" class="admin-filter-form admin-filter-booking"><input type="search" name="q" value="<?=e($q)?>" placeholder="Cari aktivitas, user, entity..."><select name="action"><option value="">Semua aktivitas</option><?php foreach($actions as $a):?><option value="<?=e($a)?>" <?=$action===$a?'selected':''?>><?=e($actionLabels[$a]??ucwords(str_replace('_',' ',$a)))?></option><?php endforeach;?></select><input type="date" name="date" value="<?=e($date)?>"><button class="small" type="submit">Filter</button><a class="button small secondary" href="activity.php">Reset</a></form>
 <div class="activity-timeline">
 <?php foreach($logs as $log):?><article class="activity-item"><div class="activity-dot"></div><div class="activity-content"><div class="activity-meta"><strong><?=e($actionLabels[$log['action']]??ucwords(str_replace('_',' ',$log['action'])))?></strong><span><?=e(date('d-m-Y H:i',strtotime($log['created_at'])))?></span></div><p><?=e($log['description']?:'Aktivitas sistem.')?></p><div class="activity-tags"><?php if($log['user_name']):?><span><?=e($log['user_name'])?> · <?=e(ucfirst($log['user_role']))?></span><?php endif;?><?php if($log['entity_type']):?><span><?=e(ucfirst($log['entity_type']))?> #<?=e($log['entity_id'])?></span><?php endif;?></div></div></article><?php endforeach;?>
 <?php if(!$logs):?><div class="empty">Belum ada aktivitas yang tercatat.</div><?php endif;?></div>
</section>
<?php require '../includes/admin_footer.php'; ?>
