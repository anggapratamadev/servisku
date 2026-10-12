<?php
session_start();
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';
require_once '../config/google.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sesuai.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, phone, role)
                 VALUES (?, ?, ?, ?, 'customer')"
            );
            $stmt->execute([$name, $email, $hashed_password, $phone]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - ServisKu</title>
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
                    <div class="auth-kicker-v5"><i></i> Mulai dari sini</div>
                    <h2>Buat akun dan mulai <em>booking servis.</em></h2>
                    <p>Simpan data kendaraan dan pantau seluruh proses servis tanpa harus mencatatnya secara manual.</p>
                    <div class="auth-features-v5">
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Tambahkan kendaraan Anda</div>
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Pilih layanan yang dibutuhkan</div>
                        <div class="auth-feature-v5"><span aria-hidden="true">✓</span> Pantau servis sampai selesai</div>
                    </div>
                </div>
            </div>
            <div class="auth-mini-card">
                <strong>Gratis untuk memulai</strong>
                <span>Setelah terdaftar, Anda dapat langsung mengakses dashboard customer.</span>
            </div>
        </section>

        <section class="auth-form-v5">
            <div class="auth-form-head">
                <div class="eyebrow">Customer</div>
                <h1>Buat akun ServisKu</h1>
                <p class="subtext">Lengkapi data berikut untuk membuat akun baru.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (google_oauth_enabled()): ?>
                <a class="google-login-btn" href="google.php"><span class="google-icon">G</span> Daftar dengan Google</a>
                <div class="auth-divider"><span>atau daftar dengan email</span></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label>Nama
                    <input type="text" name="name" placeholder="Nama lengkap" autocomplete="name" required>
                </label>

                <label>Email
                    <input type="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
                </label>

                <label>No. Telepon
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx" autocomplete="tel">
                </label>

                <label>Password
                    <input type="password" name="password" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
                </label>

                <label>Konfirmasi Password
                    <input type="password" name="confirm_password" placeholder="Ulangi password" autocomplete="new-password" required>
                </label>

                <button class="auth-submit-v5" type="submit">Buat Akun</button>
            </form>

            <div class="auth-footer-v5">Sudah punya akun? <a href="login.php">Kembali ke login</a></div>
            <div class="auth-secure-v5"><span>●</span> Data akun digunakan untuk akses sistem ServisKu</div>
        </section>
    </div>
</div>
</body>
</html>
