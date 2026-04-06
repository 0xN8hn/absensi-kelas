<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — AbsenKelas</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="layout">

    <aside>
        <div class="sidebar-logo">
            <div class="logo-icon">📋</div>
            <div class="logo-text">Absen<span>Kelas</span></div>
        </div>
        <div class="nav-section">Menu</div>
        <button class="nav-item active" id="nav-siswa" onclick="showPage('siswa')">
            <span class="nav-icon">👤</span> Kelola Siswa
        </button>
        <button class="nav-item" id="nav-rekap" onclick="showPage('rekap')">
            <span class="nav-icon">📊</span> Rekap Absensi
        </button>
        <div class="sidebar-footer">
            <a href="login.php" onclick="logout()">⬅ Keluar</a>
        </div>
    </aside>

    <main>

        <!-- PAGE: Kelola Siswa -->
        <div class="page show" id="page-siswa">
            <div class="page-header">
                <h1>Kelola Siswa</h1>
                <p>Tambah akun dan data siswa yang bisa melakukan absensi.</p>
            </div>

            <div class="grid-2">
                <div class="panel-card">
                    <div class="panel-head"><span class="panel-title">➕ Tambah Siswa Baru</span></div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" id="inpNama" placeholder="cth: Budi Santoso">
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <input type="text" id="inpKelas" placeholder="cth: XII RPL 1">
                        </div>
                        <div class="form-group">
                            <label>NIS</label>
                            <input type="text" id="inpNis" placeholder="cth: 2024001">
                        </div>
                        <div class="form-group">
                            <label>Username Login</label>
                            <input type="text" id="inpUsername" placeholder="cth: budi.santoso">
                        </div>
                        <div class="form-group">
                            <label>Password Login</label>
                            <input type="text" id="inpPassword" placeholder="cth: budi123">
                        </div>
                        <button class="btn btn-primary" id="btnTambah" style="width:100%;margin-top:4px">Tambah Siswa</button>
                        <div class="alert alert-ok"  id="alertOk"></div>
                        <div class="alert alert-err" id="alertErr"></div>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-head"><span class="panel-title">💡 Panduan</span></div>
                    <div class="panel-body">
                        <div class="tips-box">
                            <div class="tips-title">Flow Sistem</div>
                            <p>① Admin tambah data siswa + akun login</p>
                            <p>② Siswa buka <strong>login.php</strong> lalu masuk</p>
                            <p>③ Siswa pilih kelas → nama → status → GPS</p>
                            <p>④ Admin cek rekap di tab Rekap Absensi</p>
                            <div class="tips-warning">⚠ NIS dan Username harus unik per siswa.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <span class="panel-title">📋 Daftar Siswa</span>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input class="search-input" type="text" id="searchSiswa" placeholder="Cari nama / kelas...">
                        <span class="badge badge-info" id="siswaCount">0 siswa</span>
                    </div>
                </div>
                <div class="siswa-list" id="siswaList">
                    <div class="empty-state">Memuat data...</div>
                </div>
            </div>
        </div>

        <!-- PAGE: Rekap Absensi -->
        <div class="page" id="page-rekap">
            <div class="page-header">
                <h1>Rekap Absensi</h1>
                <p id="rekapSubtitle">Lihat data kehadiran siswa.</p>
            </div>

            <!-- Tab Harian / Mingguan / Bulanan -->
            <div class="rekap-tabs">
                <button class="rekap-tab active" id="tab-harian"   onclick="switchMode('harian')">📅 Harian</button>
                <button class="rekap-tab"        id="tab-mingguan" onclick="switchMode('mingguan')">📆 Mingguan</button>
                <button class="rekap-tab"        id="tab-bulanan"  onclick="switchMode('bulanan')">🗓 Bulanan</button>
            </div>

            <!-- Filter -->
            <div class="filter-bar">
                <div class="form-group" id="filterHarian">
                    <label>Tanggal</label>
                    <input type="date" id="fTgl">
                </div>
                <div class="form-group" id="filterMingguan" style="display:none">
                    <label>Pilih Minggu (pilih tanggal dalam minggu itu)</label>
                    <input type="date" id="fMinggu">
                </div>
                <div class="form-group" id="filterBulanan" style="display:none">
                    <label>Bulan</label>
                    <input type="month" id="fBulan">
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select id="fKelas">
                        <option value="">Semua Kelas</option>
                    </select>
                </div>
                <button class="btn btn-primary" id="btnRekap" style="width:auto;margin-top:0;padding:10px 20px">Tampilkan</button>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card s-total">
                    <div class="stat-label">Total Absen</div>
                    <div class="stat-value" id="stTotal">—</div>
                    <div class="stat-sub">tercatat</div>
                </div>
                <div class="stat-card s-hadir">
                    <div class="stat-label">Hadir</div>
                    <div class="stat-value" id="stHadir">—</div>
                    <div class="stat-sub" id="stPct">—</div>
                </div>
                <div class="stat-card s-izin">
                    <div class="stat-label">Izin</div>
                    <div class="stat-value" id="stIzin">—</div>
                    <div class="stat-sub">keterangan izin</div>
                </div>
                <div class="stat-card s-sakit">
                    <div class="stat-label">Sakit</div>
                    <div class="stat-value" id="stSakit">—</div>
                    <div class="stat-sub">keterangan sakit</div>
                </div>
            </div>

            <!-- Tabel -->
            <div class="panel-card">
                <div class="panel-head">
                    <span class="panel-title">Detail Kehadiran</span>
                    <span class="badge badge-info" id="rekapCount">0 data</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapBody">
                            <tr><td colspan="8" class="empty-state">Pilih periode lalu klik Tampilkan.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<script src="js/admin.js"></script>
</body>
</html>
