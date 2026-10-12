<?php
require_once '../includes/role.php';
requireRole('customer');
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE vehicle_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) $error = 'Kendaraan tidak dapat dihapus karena sudah memiliki riwayat booking. Anda tetap dapat mengedit datanya.';
        else { $stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = ? AND user_id = ?'); $stmt->execute([$id, $_SESSION['user_id']]); $success='Kendaraan dihapus.'; }
    } else {
        $plate = strtoupper(trim($_POST['plate_number'] ?? '')); $brand=trim($_POST['brand']??''); $model=trim($_POST['model']??''); $year=trim($_POST['year']??''); $color=trim($_POST['color']??'');
        if ($plate === '' || $brand === '' || $model === '') $error='Nomor polisi, merek, dan model wajib diisi.';
        elseif ($id) { $stmt=$pdo->prepare('UPDATE vehicles SET plate_number=?,brand=?,model=?,year=?,color=? WHERE id=? AND user_id=?'); $stmt->execute([$plate,$brand,$model,$year!==''?(int)$year:null,$color!==''?$color:null,$id,$_SESSION['user_id']]); $success='Kendaraan diperbarui.'; }
        else { $stmt=$pdo->prepare('INSERT INTO vehicles (user_id,plate_number,brand,model,year,color) VALUES (?,?,?,?,?,?)');$stmt->execute([$_SESSION['user_id'],$plate,$brand,$model,$year!==''?(int)$year:null,$color!==''?$color:null]);$success='Kendaraan berhasil ditambahkan.'; }
    }
}

$stmt = $pdo->prepare('SELECT * FROM vehicles WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();
$editVehicle=null; foreach($vehicles as $vv){if($vv['id']==(int)($_GET['edit']??0)){$editVehicle=$vv;break;}}
?>
<?php $pageTitle='Kendaraan Saya'; require '../includes/customer_header.php'; ?>
<?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?><?php if($success): ?><div class="success"><?=e($success)?></div><?php endif; ?>
<div class="two-column"><section class="panel"><div class="panel-heading"><div><span class="section-title">Garasi</span><h2><?=$editVehicle?'Edit Kendaraan':'Tambah Kendaraan'?></h2></div></div><form method="POST" class="compact-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($_GET['edit']??0)?>"><label>Nomor Polisi<input name="plate_number" value="<?=e($editVehicle['plate_number']??'')?>" placeholder="B 1234 ABC" required></label><label>Merek<input name="brand" value="<?=e($editVehicle['brand']??'')?>" placeholder="Honda" required></label><label>Model<input name="model" value="<?=e($editVehicle['model']??'')?>" placeholder="Brio" required></label><div class="detail-grid"><label>Tahun<input type="number" name="year" value="<?=e($editVehicle['year']??'')?>" min="1900" max="<?=date('Y')?>"></label><label>Warna<input name="color" value="<?=e($editVehicle['color']??'')?>" placeholder="Hitam"></label></div><div class="page-actions"><button type="submit">Simpan Kendaraan</button><?php if($editVehicle): ?><a class="button secondary" href="vehicles.php">Batal</a><?php endif; ?></div></form></section>
<section class="panel"><div class="panel-heading"><div><span class="section-title">Data Kendaraan</span><h2>Daftar kendaraan</h2></div><span class="muted"><?=count($vehicles)?> kendaraan</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Plat</th><th>Kendaraan</th><th>Tahun</th><th>Warna</th><th>Aksi</th></tr></thead><tbody><?php foreach($vehicles as $vehicle): ?><tr><td><strong><?=e($vehicle['plate_number'])?></strong></td><td><?=e($vehicle['brand'].' '.$vehicle['model'])?></td><td><?=e($vehicle['year']??'-')?></td><td><?=e($vehicle['color']??'-')?></td><td><div class="page-actions"><a class="button small secondary" href="vehicles.php?edit=<?=$vehicle['id']?>">Edit</a><form method="post" class="inline-form" onsubmit="return confirm('Hapus kendaraan ini?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$vehicle['id']?>"><button class="small danger" type="submit">Hapus</button></form></div></td></tr><?php endforeach; ?><?php if(!$vehicles): ?><tr><td colspan="5" class="empty">Belum ada kendaraan. Tambahkan kendaraan pertama Anda.</td></tr><?php endif; ?></tbody></table></div></section></div>
<?php require '../includes/customer_footer.php'; ?>