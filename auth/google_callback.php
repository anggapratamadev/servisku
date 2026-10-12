<?php
session_start();
require_once '../config/database.php';
require_once '../config/google.php';
require_once '../includes/helpers.php';

function google_redirect(string $query = ''): void
{
    header('Location: login.php' . ($query ? '?' . $query : ''));
    exit;
}

if (!google_oauth_enabled()) {
    google_redirect('google=not_configured');
}

if (isset($_GET['error'])) {
    google_redirect('google=cancelled');
}

$state = $_GET['state'] ?? '';
$sessionState = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);

if (!$state || !$sessionState || !hash_equals($sessionState, $state)) {
    google_redirect('google=state_error');
}

$code = $_GET['code'] ?? '';
if (!$code) {
    google_redirect('google=failed');
}

$config = google_config();
$postData = http_build_query([
    'code' => $code,
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'redirect_uri' => $config['redirect_uri'],
    'grant_type' => 'authorization_code',
], '', '&', PHP_QUERY_RFC3986);

$ch = curl_init($config['token_url']);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);
$tokenResponse = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tokenResponse === false || $curlError || $httpCode < 200 || $httpCode >= 300) {
    google_redirect('google=failed');
}

$token = json_decode($tokenResponse, true);
$accessToken = $token['access_token'] ?? '';
if (!$accessToken) {
    google_redirect('google=failed');
}

$ch = curl_init($config['userinfo_url']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken, 'Accept: application/json'],
]);
$userResponse = curl_exec($ch);
$userCurlError = curl_error($ch);
$userHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userResponse === false || $userCurlError || $userHttpCode < 200 || $userHttpCode >= 300) {
    google_redirect('google=failed');
}

$googleUser = json_decode($userResponse, true);
$googleId = trim((string) ($googleUser['sub'] ?? ''));
$email = strtolower(trim((string) ($googleUser['email'] ?? '')));
$name = trim((string) ($googleUser['name'] ?? 'Google User'));
$emailVerified = filter_var($googleUser['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

if (!$googleId || !$email || !$emailVerified || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    google_redirect('google=unverified');
}

// Find by Google ID first, then by verified email to link an existing account.
$stmt = $pdo->prepare('SELECT id, name, email, role, google_id FROM users WHERE google_id = ? LIMIT 1');
$stmt->execute([$googleId]);
$user = $stmt->fetch();

if (!$user) {
    $stmt = $pdo->prepare('SELECT id, name, email, role, google_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (!empty($user['google_id']) && !hash_equals((string) $user['google_id'], $googleId)) {
            google_redirect('google=account_conflict');
        }

        $stmt = $pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?');
        $stmt->execute([$googleId, $user['id']]);
    } else {
        $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, google_id, phone, role)
             VALUES (?, ?, ?, ?, NULL, 'customer')"
        );
        $stmt->execute([$name ?: 'Google User', $email, $randomPassword, $googleId]);

        $user = [
            'id' => $pdo->lastInsertId(),
            'name' => $name ?: 'Google User',
            'email' => $email,
            'role' => 'customer',
            'google_id' => $googleId,
        ];
    }
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
} elseif ($user['role'] === 'mechanic') {
    header('Location: ../mechanic/dashboard.php');
} else {
    header('Location: ../customer/dashboard.php');
}
exit;
