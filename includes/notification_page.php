<?php
// Shared notification-center controller. The including page must define $notificationRole and $notificationBase.
require_once __DIR__ . '/role.php';
requireRole($notificationRole);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

$uid=(int)$_SESSION['user_id'];
$error=''; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    try{
        if($action==='read' && $id>0){
            $s=$pdo->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?');$s->execute([$id,$uid]);
            $success='Notifikasi ditandai sudah dibaca.';
        }elseif($action==='read_all'){
            $s=$pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0');$s->execute([$uid]);
            $success='Semua notifikasi ditandai sudah dibaca.';
        }else throw new Exception('Aksi notifikasi tidak valid.');
    }catch(Throwable $e){$error=servisku_error_message($e);}
}
$q=$pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY is_read ASC,created_at DESC LIMIT 100');
$q->execute([$uid]);$notifications=$q->fetchAll();
$unread=notification_unread_count($pdo,$uid);
function notification_target(array $n,string $role): ?string {
    if(($n['entity_type']??'')!=='booking' || empty($n['entity_id'])) return null;
    $id=(int)$n['entity_id'];
    if($role==='customer') return 'history.php?id='.$id;
    if($role==='admin') return 'bookings.php?q='.urlencode((string)$id);
    if($role==='mechanic') return 'service.php?id='.$id;
    return null;
}
?>
