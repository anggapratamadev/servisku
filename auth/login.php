<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';
require_once '../config/google.php';

$error = '';
$success = isset($_GET['registered']);
$googleMessage = '';
if (isset($_GET['google'])) {
    $googleMessages = [
        'not_configured' => 'Login Google belum dikonfigurasi. Isi Google Client ID dan Client Secret terlebih dahulu.',
        'cancelled' => 'Login Google dibatalkan.',
        'state_error' => 'Sesi login Google tidak valid. Silakan coba lagi.',
        'failed' => 'Login Google gagal. Silakan coba lagi.',
        'unverified' => 'Akun Google harus memiliki email yang terverifikasi.',
        'account_conflict' => 'Akun Google tersebut sudah tertaut ke akun ServisKu lain.',
    ];
    $googleMessage = $googleMessages[$_GET['google']] ?? 'Login Google gagal.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role FROM users WHERE email = ? OR (role = 'admin' AND name = ?)"
    );
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
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
    }

    $error = 'Email/username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ServisKu</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=37">
</head>
<body>
<div class="auth-container auth-v5">
    <div class="auth-shell-v5">
        <section class="auth-showcase-v5">
            <div class="auth-showcase-content">
                <div class="auth-brand-v5">
                    <span class="brand-mark">SK</span>
                    <div><strong>ServisKu</strong><small>Vehicle Service Management</small></div>
                </div>

                <div>
                    <div class="auth-kicker-v5"><i></i> Sistem servis kendaraan</div>
                    <h2>Servis kendaraan jadi <em>lebih teratur.</em></h2>
                    <p>Kelola kendaraan, booking servis, proses pengerjaan, pembayaran, dan riwayat dalam satu sistem.</p>
                    <div class="auth-features-v5">
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Booking servis lebih mudah</div>
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Pantau status pengerjaan</div>
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Riwayat servis tersimpan rapi</div>
                    </div>
                </div>
            </div>

            <div class="auth-mini-card">
                <strong>ServisKu</strong>
                <span>Masuk untuk melanjutkan ke dashboard Anda.</span>
            </div>
        </section>

        <section class="auth-form-v5">
            <div class="auth-form-head">
                <div class="eyebrow">Selamat datang kembali</div>
                <h1>Masuk ke ServisKu</h1>
                <p class="subtext">Gunakan akun Anda untuk mengakses dashboard.</p>
            </div>

            <?php if ($success): ?>
                <div class="success">Registrasi berhasil. Silakan login untuk melanjutkan.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($googleMessage): ?>
                <div class="alert"><?= htmlspecialchars($googleMessage) ?></div>
            <?php endif; ?>

            <a class="google-login-btn" href="google.php" aria-label="Masuk dengan Google"><span class="google-icon" aria-hidden="true">G</span> Masuk dengan Google</a>
            <div class="auth-divider"><span>atau masuk dengan akun ServisKu</span></div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label>Email atau Username Admin
                    <input type="text" name="email" placeholder="Masukkan email atau username" autocomplete="username" required>
                </label>

                <label>Password
                    <input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                </label>

                <div class="auth-help-v5"><span>Pastikan data login Anda benar.</span><span>🔒 Aman</span></div>
                <button class="auth-submit-v5" type="submit">Masuk ke Dashboard</button>
            </form>

            <div class="auth-footer-v5">Belum punya akun? <a href="register.php">Daftar sekarang</a></div>
            <div class="auth-secure-v5"><span>●</span> Sesi login dilindungi oleh sistem keamanan ServisKu</div>
        </section>
    </div>
</div>
</body>
</html>
