<?php
$conn = new mysqli('localhost', 'root', '', '');
$conn->set_charset('utf8mb4');
$steps = [];

function run($label, $sql, $conn) {
    $ok = $conn->query($sql);
    return ['label' => $label, 'ok' => (bool)$ok, 'err' => $conn->error];
}

$steps[] = run('Buat database', "CREATE DATABASE IF NOT EXISTS `absensi_kelas` CHARACTER SET utf8mb4", $conn);
$conn->select_db('absensi_kelas');
$steps[] = run('Buat tabel siswa', "CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(30) NOT NULL,
    nis VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)", $conn);
$steps[] = run('Buat tabel absensi', "CREATE TABLE IF NOT EXISTS absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_absen TIME NOT NULL,
    latitude DECIMAL(10,8) DEFAULT 0,
    longitude DECIMAL(11,8) DEFAULT 0,
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    UNIQUE KEY unique_per_hari (siswa_id, tanggal)
)", $conn);

$error = !empty(array_filter($steps, fn($s) => !$s['ok']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup</title>
<link rel="stylesheet" href="css/style.css">
<style>
body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:24px; }
.box { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:40px; max-width:440px; width:100%; }
h1 { font-size:22px; font-weight:700; margin-bottom:4px; }
.sub { color:var(--muted); font-family:var(--mono); font-size:12px; margin-bottom:24px; }
.step { display:flex; gap:12px; padding:11px 0; border-bottom:1px solid var(--border); font-family:var(--mono); font-size:13px; }
.step:last-child { border:none; }
.err { color:var(--red); font-size:11px; margin-top:3px; }
.result { margin-top:20px; padding:13px 16px; border-radius:8px; font-weight:700; }
.ok  { background:rgba(15,217,144,.1); color:var(--green); border:1px solid rgba(15,217,144,.3); }
.err { background:rgba(240,80,110,.1); color:var(--red);   border:1px solid rgba(240,80,110,.3); }
.go  { display:block; margin-top:14px; padding:13px; border-radius:8px; background:linear-gradient(135deg,var(--accent),#8b5cf6); color:#fff; text-align:center; text-decoration:none; font-weight:700; }
</style>
</head>
<body>
<div class="box">
    <h1>⚙️ Setup Database</h1>
    <div class="sub">Jalankan sekali. Aman diulang, data tidak terhapus.</div>
    <?php foreach ($steps as $s): ?>
    <div class="step">
        <span><?= $s['ok'] ? '✅' : '❌' ?></span>
        <div><?= htmlspecialchars($s['label']) ?>
            <?php if (!$s['ok']): ?><div class="err"><?= htmlspecialchars($s['err']) ?></div><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$error): ?>
        <div class="result ok">✓ Database siap!</div>
        <a class="go" href="login.php">Masuk ke Admin →</a>
    <?php else: ?>
        <div class="result err">❌ Gagal. Pastikan XAMPP nyala.</div>
    <?php endif; ?>
</div>
</body>
</html>
