<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Absensi — AbsenKelas</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/index.css">
</head>
<body>

<nav>
    <div class="logo">
        <div class="logo-icon">📋</div>
        AbsenKelas
    </div>
    <div class="nav-user">
        Halo, <strong id="namaUser">—</strong>
        <button class="btn-logout" onclick="logout()">Keluar</button>
    </div>
</nav>

<div class="card">

    <div class="datebar">
        <div>
            <div class="date-label">Hari ini</div>
            <div class="date-value" id="tglDisplay">—</div>
        </div>
        <div class="clock" id="clock">--:--:--</div>
    </div>

    <!-- Steps -->
    <div class="steps">
        <div class="step-wrap">
            <div class="step-circle active" id="sc1">1</div>
            <div class="step-label">Kelas</div>
        </div>
        <div class="step-line" id="sl1"></div>
        <div class="step-wrap">
            <div class="step-circle" id="sc2">2</div>
            <div class="step-label">Nama</div>
        </div>
        <div class="step-line" id="sl2"></div>
        <div class="step-wrap">
            <div class="step-circle" id="sc3">3</div>
            <div class="step-label">Status</div>
        </div>
        <div class="step-line" id="sl3"></div>
        <div class="step-wrap">
            <div class="step-circle" id="sc4">4</div>
            <div class="step-label">Lokasi</div>
        </div>
        <div class="step-line" id="sl4"></div>
        <div class="step-wrap">
            <div class="step-circle" id="sc5">5</div>
            <div class="step-label">Selesai</div>
        </div>
    </div>

    <!-- Step 1: Kelas -->
    <div class="panel show" id="p1">
        <div class="form-group">
            <label>Pilih Kelas</label>
            <select id="selKelas">
                <option value="">— Pilih kelas —</option>
            </select>
        </div>
        <button class="btn btn-primary" id="btnKelas" disabled>Lanjut →</button>
    </div>

    <!-- Step 2: Nama -->
    <div class="panel" id="p2">
        <div class="form-group">
            <label>Pilih Nama Kamu</label>
            <select id="selSiswa">
                <option value="">— Pilih nama —</option>
            </select>
        </div>
        <button class="btn btn-primary" id="btnSiswa" disabled>Lanjut →</button>
        <button class="btn btn-ghost" id="btnBack1">← Kembali</button>
    </div>

    <!-- Step 3: Status -->
    <div class="panel" id="p3">
        <div class="form-group">
            <label>Pilih Status Kehadiran</label>
            <div class="status-grid">
                <div class="status-btn" id="btnHadir" onclick="pilihStatus('hadir')">
                    <span class="status-emoji">✅</span>
                    <span class="status-label">Hadir</span>
                    <span class="status-desc">Masuk sekolah</span>
                </div>
                <div class="status-btn" id="btnIzin" onclick="pilihStatus('izin')">
                    <span class="status-emoji">📝</span>
                    <span class="status-label">Izin</span>
                    <span class="status-desc">Ada keperluan</span>
                </div>
                <div class="status-btn" id="btnSakit" onclick="pilihStatus('sakit')">
                    <span class="status-emoji">🤒</span>
                    <span class="status-label">Sakit</span>
                    <span class="status-desc">Tidak sehat</span>
                </div>
            </div>
        </div>

        <!-- Keterangan muncul kalau izin/sakit -->
        <div class="keterangan-box" id="keteranganBox">
            <div class="form-group">
                <label id="keteranganLabel">Keterangan Izin <span style="color:var(--red)">*</span></label>
                <textarea id="inpKeterangan" placeholder="Jelaskan alasan izin/sakit kamu..."></textarea>
            </div>
        </div>

        <button class="btn btn-primary" id="btnStatus" disabled>Lanjut →</button>
        <button class="btn btn-ghost" id="btnBack2">← Kembali</button>
    </div>

    <!-- Step 4: Lokasi -->
    <div class="panel" id="p4">
        <div class="form-group">
            <label>Lokasi GPS</label>
            <div class="loc-box" id="locBox">
                <div class="loc-icon" id="locIcon">📍</div>
                <div class="loc-text" id="locText">Mengambil lokasi...</div>
                <div class="loc-pulse" id="locPulse"></div>
            </div>
        </div>
        <button class="btn btn-primary" id="btnLokasi" disabled>Lanjut →</button>
        <button class="btn btn-ghost" id="btnBack3">← Kembali</button>
    </div>

    <!-- Step 5: Konfirmasi -->
    <div class="panel" id="p5">
        <div class="info-grid" id="confirmGrid"></div>
        <div class="alert alert-err" id="alertAbsen"></div>
        <button class="btn btn-primary" id="btnAbsen">✓ Kirim Absensi</button>
        <button class="btn btn-ghost" id="btnBack4">← Kembali</button>
    </div>

    <!-- Sukses -->
    <div class="panel" id="pSukses">
        <div class="success-wrap">
            <div class="success-icon">✓</div>
            <h2>Absensi Terkirim!</h2>
            <p>Data kehadiranmu sudah tercatat.</p>
            <div class="info-grid" id="resultGrid"></div>
            <button class="btn btn-primary" id="btnUlang">Absen Siswa Lain</button>
        </div>
    </div>

</div>

<script src="js/index.js"></script>
</body>
</html>
