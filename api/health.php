<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode([
    'success' => true,
    'service' => 'ServisKu API',
    'version' => '1.0',
    'status' => 'ok',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
