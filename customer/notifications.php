<?php
$notificationRole='customer';$notificationBase='customer';
require '../includes/notification_page.php';
$pageTitle='Notifikasi';require '../includes/customer_header.php';
?>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<section class="panel notification-center"><div class="panel-heading"><div><span class="muted">Pusat informasi</span><h2>Notifikasi</h2><p class="muted"><?=e((string)$unread)?> notifikasi belum dibaca</p></div><?php if($unread):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="read_all"><button class="small secondary">Tandai semua dibaca</button></form><?php endif;?></div>
<div class="notification-list">
<?php foreach($notifications as $n):$target=notification_target($n,'customer');?><article class="notification-item <?=$n['is_read']?'read':'unread'?>"><div class="notification-icon type-<?=e($n['type'])?>">●</div><div class="notification-content"><div class="notification-meta"><strong><?=e($n['title'])?></strong><time><?=e(date('d M Y H:i',strtotime($n['created_at'])))?></time></div><p><?=e($n['message'])?></p><div class="notification-actions"><?php if($target):?><a class="button small secondary" href="<?=e($target)?>">Lihat detail</a><?php endif;?><?php if(!$n['is_read']):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?=$n['id']?>"><button class="small">Tandai dibaca</button></form><?php endif;?></div></div></article><?php endforeach;?>
<?php if(!$notifications):?><div class="notification-empty"><strong>Belum ada notifikasi</strong><span>Aktivitas penting dari servis Anda akan muncul di sini.</span></div><?php endif;?></div></section>
<?php require '../includes/customer_footer.php';?>
