<?php
/**
 * Safe Google OAuth configuration loader.
 * Credentials are read from environment variables or ignored config/google.local.php.
 * Never place production secrets in this file or commit them to Git.
 */
function google_config(): array
{
    $localFile = __DIR__ . '/google.local.php';
    $local = is_file($localFile) ? require $localFile : [];
    if (!is_array($local)) {
        $local = [];
    }

    return [
        'client_id' => (string) ($local['client_id'] ?? getenv('GOOGLE_CLIENT_ID') ?: ''),
        'client_secret' => (string) ($local['client_secret'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: ''),
        'redirect_uri' => (string) ($local['redirect_uri'] ?? getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/servisku/auth/google_callback.php'),
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://openidconnect.googleapis.com/v1/userinfo',
    ];
}

function google_oauth_enabled(): bool
{
    $config = google_config();
    return $config['client_id'] !== '' && $config['client_secret'] !== '';
}
