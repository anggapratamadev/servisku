<?php
require_once '../includes/role.php'; requireRole('admin'); require_once '../config/database.php';
$q=trim($_GET['q']??'');
$sql="SELECT v.*,u.name customer_name,u.email FROM vehicles v JOIN users u ON u.id=v.user_id"; $params=[]; if($q!==''){ $sql.=" WHERE v.plate_number LIKE ? OR v.brand LIKE ? OR v.model LIKE ? OR u.name LIKE ?"; $like="%$q%"; $params=[$like,$like,$like,$like]; } $sql.=" ORDER BY v.created_at DESC"; $stmt=$pdo->prepare($sql); $stmt->execute($params); $vehicles=$stmt->fetchAll();
$pageTitle='Data Kendaraan'; require '../includes/admin_header.php';
?>
<section class="panel"><div class="panel-heading"><h2>Seluruh Kendaraan</h2><span class="muted"><?= count($vehicles) ?> hasil</span></div><form method="get" class="admin-filter-form"><input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari plat, merk, model, atau customer..."><button class="small" type="submit">Cari</button><?php if($q!==''): ?><a class="button small secondary" href="vehicles.php">Reset</a><?php endif; ?></form><div class="table-wrap"><table class="data-table"><thead><tr><th>Customer</th><th>Plat</th><th>Kendaraan</th><th>Tahun</th><th>Warna</th><th>Terdaftar</th></tr></thead><tbody>
<?php foreach($vehicles as $v): ?><tr><td><?= htmlspecialchars($v['customer_name']) ?><small><?= htmlspecialchars($v['email']) ?></small></td><td><strong><?= htmlspecialchars($v['plate_number']) ?></strong></td><td><?= htmlspecialchars($v['brand'].' '.$v['model']) ?></td><td><?= htmlspecialchars($v['year']??'-') ?></td><td><?= htmlspecialchars($v['color']??'-') ?></td><td><?= htmlspecialchars(date('d-m-Y',strtotime($v['created_at']))) ?></td></tr><?php endforeach; ?>
<?php if(!$vehicles): ?><tr><td colspan="6" class="empty">Belum ada kendaraan.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require '../includes/admin_footer.php'; ?>
