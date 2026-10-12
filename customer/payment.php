<?php
require_once '../includes/role.php'; requireRole('customer');
require_once '../config/database.php'; require_once '../includes/helpers.php'; require_once '../includes/csrf.php';

$id=(int)($_GET['id']??$_POST['booking_id']??0);
$q=$pdo->prepare("SELECT b.*,s.name service_name,b.service_price FROM bookings b JOIN services s ON s.id=b.service_id WHERE b.id=? AND b.user_id=? AND b.status IN ('waiting_payment','paid')");
$q->execute([$id,$_SESSION['user_id']]);
$b=$q->fetch();
if(!$b) exit('Invoice tidak ditemukan atau belum dapat dibayar.');

$parts=$pdo->prepare('SELECT sp.quantity,sp.price FROM service_parts sp JOIN service_records sr ON sr.id=sp.service_record_id WHERE sr.booking_id=?');
$parts->execute([$id]);
$total=(float)$b['service_price'];
foreach($parts->fetchAll() as $p) $total+=(float)$p['price']*$p['quantity'];

// Demo payment accounts. Replace these values with the real business accounts before production.
$bankAccounts=[
    ['code'=>'bca','name'=>'BCA','number'=>'1234567890','holder'=>'ServisKu'],
    ['code'=>'bri','name'=>'BRI','number'=>'0987654321','holder'=>'ServisKu'],
    ['code'=>'mandiri','name'=>'Mandiri','number'=>'1122334455','holder'=>'ServisKu'],
    ['code'=>'bni','name'=>'BNI','number'=>'6677889900','holder'=>'ServisKu'],
];

$payStmt=$pdo->prepare('SELECT * FROM payments WHERE booking_id=?');
$payStmt->execute([$id]);
$payment=$payStmt->fetch();
$error=''; $success='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $method=$_POST['method']??'';
    $bankCode=$_POST['bank']??'';
    $selectedBank=array_values(array_filter($bankAccounts,fn($bank)=>$bank['code']===$bankCode))[0]??null;

    if($b['status']==='paid' || ($payment && $payment['status']==='paid')) {
        $error='Pembayaran sudah dikonfirmasi sebagai lunas.';
    } elseif(!in_array($method,['transfer','qris','cash'],true)) {
        $error='Metode pembayaran tidak valid.';
    } elseif($method==='transfer' && !$selectedBank) {
        $error='Silakan pilih bank tujuan.';
    } elseif(in_array($method,['transfer','qris'],true) && (!isset($_FILES['proof']) || $_FILES['proof']['error']!==UPLOAD_ERR_OK)) {
        $error='Bukti pembayaran wajib diunggah untuk Transfer atau QRIS.';
    } else {
        try {
            $proofFile=$payment['proof_file']??null;
            if(in_array($method,['transfer','qris'],true)) {
                $file=$_FILES['proof'];
                if($file['size']>3*1024*1024) throw new Exception('Ukuran bukti pembayaran maksimal 3 MB.');
                $finfo=new finfo(FILEINFO_MIME_TYPE);
                $mime=$finfo->file($file['tmp_name']);
                $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if(!isset($allowed[$mime])) throw new Exception('Bukti pembayaran harus berupa JPG, PNG, atau WEBP.');
                $dir=dirname(__DIR__).'/uploads/payment_proofs';
                if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new Exception('Folder bukti pembayaran tidak dapat dibuat.');
                $proofFile='payment_proofs/'.bin2hex(random_bytes(16)).'.'.$allowed[$mime];
                if(!move_uploaded_file($file['tmp_name'],$dir.'/'.basename($proofFile))) throw new Exception('Bukti pembayaran gagal diunggah.');
            } else {
                $proofFile=null;
            }

            $pdo->beginTransaction();
            $s=$pdo->prepare('SELECT id,status,proof_file FROM payments WHERE booking_id=? FOR UPDATE'); $s->execute([$id]); $pay=$s->fetch();
            if($pay && $pay['status']==='paid') throw new Exception('Pembayaran sudah dikonfirmasi.');
            if($pay) {
                $s=$pdo->prepare("UPDATE payments SET amount=?,method=?,status='pending',proof_file=?,submitted_at=NOW(),verified_by=NULL,verified_at=NULL,verification_note=NULL,paid_at=NULL WHERE id=?");
                $s->execute([$total,$method,$proofFile,$pay['id']]);
            } else {
                $s=$pdo->prepare("INSERT INTO payments(booking_id,amount,method,status,proof_file,submitted_at) VALUES(?,?,?,'pending',?,NOW())");
                $s->execute([$id,$total,$method,$proofFile]);
            }
            $pdo->commit();
            notify_role($pdo, 'admin', 'Pembayaran baru', 'Customer mengirim pembayaran dan menunggu verifikasi admin.', 'payment', 'booking', $id);
            log_activity($pdo, 'payment_submitted', 'Customer mengirim pembayaran untuk diverifikasi.', 'booking', $id);
            $success='Pembayaran berhasil dikirim. Admin akan memeriksa bukti pembayaran sebelum status menjadi Lunas.';
            $payStmt=$pdo->prepare('SELECT * FROM payments WHERE booking_id=?'); $payStmt->execute([$id]); $payment=$payStmt->fetch();
        } catch(Throwable $e) {
            if($pdo->inTransaction()) $pdo->rollBack();
            $error=servisku_error_message($e);
        }
    }
}

$pageTitle='Pembayaran #'.$id; require '../includes/customer_header.php';
?>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<section class="panel narrow payment-page">
    <div class="panel-heading"><div><span class="muted">Invoice</span><h2>#INV-<?=str_pad($id,6,'0',STR_PAD_LEFT)?></h2></div><?php if($payment):?><span class="badge badge-<?=$payment['status']?>"><?=e($payment['status']==='pending'?'Menunggu Verifikasi':payment_status_label($payment['status']))?></span><?php endif;?></div>
    <div class="payment-summary"><p>Layanan: <strong><?=e($b['service_name'])?></strong></p><div class="invoice-total">Total <?=rupiah($total)?></div></div>
    <?php if($payment && $payment['status']==='pending'):?>
        <div class="payment-info"><strong>Menunggu pemeriksaan admin</strong><p>Bukti pembayaran Anda sudah diterima. Admin akan memeriksa bukti sebelum pembayaran dinyatakan lunas.</p></div>
        <?php if($payment['proof_file']):?><p><strong>Bukti pembayaran:</strong> Sudah diterima dan menunggu pemeriksaan admin.</p><?php endif;?>
    <?php elseif($payment && $payment['status']==='failed'):?>
        <div class="alert"><strong>Bukti pembayaran ditolak.</strong><br><?=e($payment['verification_note']?:'Silakan kirim ulang bukti pembayaran yang sesuai.')?></div>
        <?php include __DIR__.'/payment_form.php'; ?>
    <?php elseif($b['status']!=='paid'):?>
        <?php include __DIR__.'/payment_form.php'; ?>
        <p class="muted">Mode demo/portfolio: belum terhubung payment gateway otomatis.</p>
    <?php else:?>
        <div class="success inline-message">Pembayaran sudah dikonfirmasi sebagai lunas.</div>
    <?php endif;?>
    <a class="button secondary" href="history.php?id=<?=$id?>">Kembali ke Detail Servis</a>
</section>
<script>
const method=document.getElementById('payment-method'), proof=document.getElementById('proof-field'), bankBox=document.getElementById('bank-field'), qrisBox=document.getElementById('qris-box');
function syncPaymentFields(){
    if(!method)return;
    const transfer=method.value==='transfer', proofRequired=method.value!=='cash';
    if(proof) { proof.style.display=proofRequired?'grid':'none'; const input=proof.querySelector('input'); if(input)input.required=proofRequired; }
    if(bankBox) { bankBox.style.display=transfer?'block':'none'; const radios=bankBox.querySelectorAll('input[name=bank]'); radios.forEach(r=>r.required=transfer && ![...radios].some(x=>x.checked)); }
    if(qrisBox) qrisBox.style.display=method.value==='qris'?'block':'none';
}
if(method){method.addEventListener('change',syncPaymentFields);syncPaymentFields();}
</script>
<?php require '../includes/customer_footer.php';?>
