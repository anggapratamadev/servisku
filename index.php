<?php
session_start();
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') $target = 'admin/dashboard.php';
    elseif ($_SESSION['user_role'] === 'mechanic') $target = 'mechanic/dashboard.php';
    else $target = 'customer/dashboard.php';
    header("Location: $target");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="ServisKu adalah platform manajemen servis kendaraan dan booking bengkel untuk customer, admin, dan mekanik.">
<title>ServisKu — Manajemen Servis Kendaraan</title>
<link rel="stylesheet" href="assets/css/style.css?v=37">
</head>
<body class="landing-page">
<header class="landing-nav">
  <a class="landing-brand" href="index.php" aria-label="ServisKu Home">
    <span class="brand-mark">SK</span>
    <span><strong>ServisKu</strong><small>Vehicle Service Platform</small></span>
  </a>
  <nav class="landing-menu">
    <a href="#fitur">Fitur</a>
    <a href="#alur">Cara Kerja</a>
    <a href="#role">Role</a>
    <a href="#tentang">Tentang</a>
    <a class="landing-login" href="auth/login.php">Login</a>
    <a class="button landing-register" href="auth/register.php">Daftar Gratis</a>
  </nav>
</header>

<main>
<section class="landing-hero">
  <div class="hero-glow hero-glow-one"></div><div class="hero-glow hero-glow-two"></div>
  <div class="hero-content">
    <div class="hero-badge"><span></span> Manajemen bengkel digital</div>
    <h1>Kelola servis kendaraan dengan <em>lebih rapi.</em></h1>
    <p class="hero-lead">Booking, inspeksi, mekanik, sparepart, invoice, pembayaran, dan riwayat servis dalam satu sistem yang sederhana dan terstruktur.</p>
    <div class="hero-actions">
      <a class="button hero-primary" href="auth/register.php">Mulai Sekarang <span>→</span></a>
      <a class="hero-secondary" href="auth/login.php"><span class="play-icon">↗</span> Masuk ke Dashboard</a>
    </div>
    <div class="hero-proof">
      <div><strong>3</strong><span>Role terintegrasi</span></div>
      <div><strong>1</strong><span>Alur servis terpadu</span></div>
      <div><strong>24/7</strong><span>Akses data servis</span></div>
    </div>
  </div>

  <div class="hero-dashboard-wrap">
    <div class="dashboard-glow"></div>
    <div class="mock-dashboard">
      <div class="mock-windowbar"><div><i></i><i></i><i></i></div><span>servisku.app / dashboard</span><b>•••</b></div>
      <div class="mock-layout">
        <aside class="mock-sidebar">
          <div class="mock-logo"><b>SK</b><span>ServisKu</span></div>
          <small>Menu</small>
          <div class="mock-nav active"><b>⌂</b> Dashboard</div><div class="mock-nav"><b>▣</b> Booking</div><div class="mock-nav"><b>⚙</b> Kendaraan</div><div class="mock-nav"><b>◈</b> Riwayat</div>
          <div class="mock-side-bottom"><span></span><span></span></div>
        </aside>
        <div class="mock-main">
          <div class="mock-heading"><div><small>Ringkasan</small><h3>Selamat datang kembali 👋</h3></div><div class="mock-avatar">A</div></div>
          <div class="mock-cards"><div><span>Total Booking</span><strong>24</strong><b>+12% bulan ini</b></div><div><span>Diproses</span><strong>08</strong><b>Aktif sekarang</b></div><div><span>Selesai</span><strong>16</strong><b>+8 servis</b></div></div>
          <div class="mock-chart"><div class="mock-chart-head"><strong>Aktivitas Servis</strong><span>7 hari terakhir</span></div><div class="chart-bars"><i style="height:35%"></i><i style="height:52%"></i><i style="height:44%"></i><i style="height:68%"></i><i style="height:58%"></i><i style="height:82%"></i><i style="height:72%"></i></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="landing-strip">
  <div><span class="strip-dot"></span><strong>Workflow terintegrasi</strong></div>
  <span>Customer</span><i>→</i><span>Admin</span><i>→</i><span>Mechanic</span><i>→</i><span>Payment</span>
</section>

<section id="fitur" class="landing-section feature-section">
  <div class="section-heading"><div><span class="eyebrow">Fitur utama</span><h2>Semua proses bengkel,<br><strong>dalam satu tempat.</strong></h2></div><p>ServisKu dirancang untuk membuat proses servis lebih transparan, mudah dipantau, dan tidak tercecer.</p></div>
  <div class="feature-grid-modern">
    <article class="feature-card feature-large"><div class="feature-icon-modern">🚗</div><h3>Vehicle Management</h3><p>Simpan kendaraan customer lengkap dengan nomor polisi, brand, model, dan tahun kendaraan.</p><a href="auth/register.php">Kelola kendaraan <span>↗</span></a></article>
    <article class="feature-card"><div class="feature-icon-modern">◷</div><h3>Smart Booking</h3><p>Customer memilih layanan, kendaraan, tanggal, dan keluhan dalam alur booking yang jelas.</p></article>
    <article class="feature-card"><div class="feature-icon-modern">🔧</div><h3>Mechanic Workflow</h3><p>Assignment mekanik, inspeksi, diagnosis, sparepart, hingga pekerjaan selesai tercatat.</p></article>
    <article class="feature-card"><div class="feature-icon-modern">▤</div><h3>Invoice & Payment</h3><p>Biaya jasa dan sparepart dirangkum menjadi invoice serta status pembayaran yang mudah dilacak.</p></article>
  </div>
</section>

<section id="alur" class="landing-section workflow-modern-section">
  <div class="section-heading centered"><span class="eyebrow">Cara kerja</span><h2>Dari booking sampai selesai,<br><strong>semuanya punya alur.</strong></h2><p>Setiap role bekerja pada bagian yang tepat sehingga status servis tetap terkontrol.</p></div>
  <div class="workflow-modern">
    <div class="workflow-line"></div>
    <article><span>01</span><div class="workflow-icon">＋</div><h3>Booking</h3><p>Customer memilih kendaraan dan layanan yang dibutuhkan.</p></article>
    <article><span>02</span><div class="workflow-icon">✓</div><h3>Verifikasi</h3><p>Admin memeriksa booking dan menentukan mekanik yang tersedia.</p></article>
    <article><span>03</span><div class="workflow-icon">⚙</div><h3>Pengerjaan</h3><p>Mekanik melakukan inspeksi, diagnosis, sparepart, dan pekerjaan.</p></article>
    <article><span>04</span><div class="workflow-icon">Rp</div><h3>Pembayaran</h3><p>Invoice dibuat, pembayaran dicatat, dan riwayat tersimpan.</p></article>
  </div>
</section>

<section id="role" class="landing-section role-section">
  <div class="role-copy"><span class="eyebrow">Untuk setiap role</span><h2>Satu sistem,<br><strong>tiga workspace.</strong></h2><p>Setiap pengguna mendapatkan tampilan dan fitur sesuai tanggung jawabnya.</p><a class="button" href="auth/register.php">Coba ServisKu →</a></div>
  <div class="role-cards">
    <article><div class="role-icon customer">C</div><div><span>Customer</span><h3>Customer Workspace</h3><p>Booking, kendaraan, status servis, invoice, pembayaran, dan riwayat.</p></div></article>
    <article><div class="role-icon admin">A</div><div><span>Admin</span><h3>Admin Workspace</h3><p>Kelola customer, kendaraan, layanan, mekanik, booking, pembayaran, dan laporan.</p></div></article>
    <article><div class="role-icon mechanic">M</div><div><span>Mechanic</span><h3>Mechanic Workspace</h3><p>Lihat job, inspeksi kendaraan, gunakan sparepart, catat pekerjaan, dan selesaikan servis.</p></div></article>
  </div>
</section>

<section id="tentang" class="landing-section about-modern">
  <div class="about-card"><div class="about-mark">SK</div><span class="eyebrow">Tentang ServisKu</span><h2>Built for a cleaner<br><strong>workshop workflow.</strong></h2><p>ServisKu adalah project web manajemen servis kendaraan berbasis PHP Native dan MySQL. Fokusnya bukan hanya CRUD, tetapi bagaimana seluruh proses servis dapat berjalan dari booking hingga pembayaran.</p><div class="tech-tags"><span>PHP Native</span><span>MySQL</span><span>HTML / CSS</span><span>JavaScript</span><span>Role Based Access</span></div></div>
  <div class="about-side"><div><strong>100%</strong><span>Workflow berbasis role</span></div><div><strong>1</strong><span>Platform untuk seluruh proses</span></div><div><strong>∞</strong><span>Riwayat servis terstruktur</span></div></div>
</section>

<section class="landing-cta"><div><span class="eyebrow">Siap mulai?</span><h2>Mulai kelola servis dengan<br><strong>cara yang lebih modern.</strong></h2></div><a class="button" href="auth/register.php">Buat Akun Customer <span>→</span></a></section>
</main>

<footer class="landing-footer"><div class="footer-brand"><span class="brand-mark">SK</span><div><strong>ServisKu</strong><small>Vehicle Service Management & Booking System</small></div></div><div class="footer-links"><a href="#fitur">Fitur</a><a href="#alur">Cara Kerja</a><a href="#tentang">Tentang</a><a href="auth/login.php">Login</a></div><span class="footer-copy">© <?= date('Y') ?> ServisKu</span></footer>
</body>
</html>
