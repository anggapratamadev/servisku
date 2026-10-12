<?php
require_once '../includes/role.php'; requireRole('admin');
require_once '../config/database.php';
$id=(int)($_GET['id']??0);
$q=$pdo->prepare('SELECT proof_file FROM payments WHERE id=?'); $q->execute([$id]); $row=$q->fetch();
if(!$row || !$row['proof_file']) { http_response_code(404); exit('Bukti pembayaran tidak ditemukan.'); }
$base=realpath(dirname(__DIR__).'/uploads/payment_proofs');
$file=realpath(dirname(__DIR__).'/uploads/'.$row['proof_file']);
if(!$base || !$file || !str_starts_with($file,$base.DIRECTORY_SEPARATOR) || !is_file($file)){http_response_code(404);exit('File bukti tidak ditemukan.');}
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);
$allowed=['image/jpeg','image/png','image/webp']; if(!in_array($mime,$allowed,true)){http_response_code(403);exit('Format file tidak diizinkan.');}
header('Content-Type: '.$mime); header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, max-age=300'); readfile($file);
