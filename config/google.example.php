<?php
/**
 * Copy to config/google.local.php and replace the placeholders.
 * Keep google.local.php out of Git. Never commit real OAuth credentials.
 */
return [
    'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    'redirect_uri' => 'http://localhost/servisku/auth/google_callback.php',
];
