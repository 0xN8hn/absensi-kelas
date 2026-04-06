<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — AbsenKelas</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="tech-dots"></div>

<div class="login-wrap">
    <div class="login-logo">
        <div class="logo-icon">📋</div>
        <h1>AbsenKelas</h1>
        <p>Sistem Absensi Digital</p>
    </div>

    <!-- Tab pilih role -->
    <div class="role-tabs">
        <button class="role-tab active" id="tabSiswa" onclick="switchRole('siswa')">👤 Login Siswa</button>
        <button class="role-tab" id="tabAdmin" onclick="switchRole('admin')">🔧 Login Admin</button>
    </div>

    <div class="login-card">
        <h2 id="loginTitle">Halo, Siswa!</h2>
        <p class="subtitle" id="loginSubtitle">Masuk untuk melakukan absensi hari ini.</p>

        <div class="login-alert" id="alertErr"></div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" id="username" placeholder="Masukkan username kamu">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" placeholder="Masukkan password">
        </div>

        <button class="btn-login" onclick="doLogin()">Masuk →</button>
    </div>
</div>

<script src="js/login.js"></script>
</body>
</html>
