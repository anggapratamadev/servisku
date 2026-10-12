<?php
session_start();
require_once '../config/google.php';

if (!google_oauth_enabled()) {
    header('Location: login.php?google=not_configured');
    exit;
}

$config = google_config();
$state = bin2hex(random_bytes(32));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'include_granted_scopes' => 'true',
    'prompt' => 'select_account',
];

header('Location: ' . $config['auth_url'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
exit;
