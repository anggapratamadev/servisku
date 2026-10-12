<?php
require_once '../includes/role.php'; requireRole('admin'); require_once '../config/database.php'; require_once '../includes/csrf.php';
$error=''; $success='';
$q=trim($_GET['q']??'');
if ($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf();
    $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??'');
    if ($id<1 || $name==='') $error='Data customer tidak valid.';
    else { $stmt=$pdo->prepare("UPDATE users SET name=?, phone=? WHERE id=? AND role='customer'"); $stmt->execute([$name,$phone!==''?$phone:null,$id]); $success='Data customer berhasil diperbarui.'; }
}
$sql="SELECT u.id,u.name,u.email,u.phone,u.created_at,COUNT(DISTINCT v.id) vehicle_count,COUNT(DISTINCT b.id) booking_count FROM users u LEFT JOIN vehicles v ON v.user_id=u.id LEFT JOIN bookings b ON b.user_id=u.id WHERE u.role='customer'";
$params=[]; if($q!==''){ $sql.=" AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like]; } $sql.=" GROUP BY u.id ORDER BY u.created_at DESC"; $stmt=$pdo->prepare($sql); $stmt->execute($params); $customers=$stmt->fetchAll();
$pageTitle='Data Customer'; require '../includes/admin_header.php';
?>
<?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<section class="panel"><div class="panel-heading"><h2>Customer Terdaftar</h2><span class="muted"><?= count($customers) ?> hasil</span></div><form method="get" class="admin-filter-form"><input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama, email, atau telepon..."><button class="small" type="submit">Cari</button><?php if($q!==''): ?><a class="button small secondary" href="customers.php">Reset</a><?php endif; ?></form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Kendaraan</th><th>Booking</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($customers as $c): ?><tr><td><?= htmlspecialchars($c['name']) ?></td><td><?= htmlspecialchars($c['email']) ?></td><td><?= htmlspecialchars($c['phone']?:'-') ?></td><td><?= $c['vehicle_count'] ?></td><td><?= $c['booking_count'] ?></td><td><details><summary class="button small secondary">Edit</summary><form method="post" class="compact-form"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="id" value="<?= $c['id'] ?>"><label>Nama<input name="name" value="<?= htmlspecialchars($c['name']) ?>" required></label><label>Telepon<input name="phone" value="<?= htmlspecialchars($c['phone']??'') ?>"></label><button type="submit">Simpan</button></form></details></td></tr><?php endforeach; ?>
<?php if(!$customers): ?><tr><td colspan="6" class="empty">Belum ada customer.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require '../includes/admin_footer.php'; ?>
