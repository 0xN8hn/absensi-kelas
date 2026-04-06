<?php
require 'db.php';
header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? '';

// ── LOGIN ──────────────────────────────────────────────
if ($action === 'login') {
    $body = json_decode(file_get_contents('php://input'), true);
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        // Kalau siswa, ambil data siswa juga
        $siswaData = null;
        if ($user['role'] === 'siswa') {
            $s = $conn->prepare("SELECT id, nama, kelas, nis FROM siswa WHERE user_id = ?");
            $s->bind_param('i', $user['id']);
            $s->execute();
            $siswaData = $s->get_result()->fetch_assoc();
        }
        echo json_encode(['ok' => true, 'role' => $user['role'], 'user_id' => $user['id'], 'siswa' => $siswaData]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Username atau password salah!']);
    }
}

// ── GET KELAS ──────────────────────────────────────────
else if ($action === 'get_kelas') {
    $r = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
    $data = [];
    while ($row = $r->fetch_assoc()) $data[] = $row['kelas'];
    echo json_encode(['ok' => true, 'data' => $data]);
}

// ── GET SISWA ──────────────────────────────────────────
else if ($action === 'get_siswa') {
    $kelas = $_GET['kelas'] ?? '';
    $stmt  = $conn->prepare("SELECT id, nama, nis FROM siswa WHERE kelas = ? ORDER BY nama");
    $stmt->bind_param('s', $kelas);
    $stmt->execute();
    echo json_encode(['ok' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

// ── GET SEMUA SISWA (admin) ────────────────────────────
else if ($action === 'get_semua_siswa') {
    $r = $conn->query("SELECT s.id, s.nama, s.kelas, s.nis, u.username FROM siswa s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.kelas, s.nama");
    echo json_encode(['ok' => true, 'data' => $r->fetch_all(MYSQLI_ASSOC)]);
}

// ── TAMBAH SISWA + USER (admin) ────────────────────────
else if ($action === 'tambah_siswa') {
    $body     = json_decode(file_get_contents('php://input'), true);
    $nama     = trim($body['nama']     ?? '');
    $kelas    = trim($body['kelas']    ?? '');
    $nis      = trim($body['nis']      ?? '');
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');

    if (!$nama || !$kelas || !$nis || !$username || !$password) {
        echo json_encode(['ok' => false, 'msg' => 'Semua kolom wajib diisi!']); return;
    }

    // Buat akun user dulu
    $s = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'siswa')");
    $s->bind_param('ss', $username, $password);
    if (!$s->execute()) {
        $msg = str_contains($conn->error, 'Duplicate') ? "Username '$username' sudah dipakai!" : $conn->error;
        echo json_encode(['ok' => false, 'msg' => $msg]); return;
    }
    $userId = $conn->insert_id;

    // Buat data siswa
    $s2 = $conn->prepare("INSERT INTO siswa (user_id, nama, kelas, nis) VALUES (?, ?, ?, ?)");
    $s2->bind_param('isss', $userId, $nama, $kelas, $nis);
    if ($s2->execute()) {
        echo json_encode(['ok' => true, 'msg' => "Siswa $nama berhasil ditambahkan."]);
    } else {
        $msg = str_contains($conn->error, 'Duplicate') ? "NIS $nis sudah terdaftar!" : $conn->error;
        // Rollback: hapus user yang baru dibuat
        $conn->query("DELETE FROM users WHERE id = $userId");
        echo json_encode(['ok' => false, 'msg' => $msg]);
    }
}

// ── HAPUS SISWA (admin) ────────────────────────────────
else if ($action === 'hapus_siswa') {
    $id = intval($_POST['id'] ?? 0);
    // Hapus user terkait juga
    $getUser = $conn->prepare("SELECT user_id FROM siswa WHERE id = ?");
    $getUser->bind_param('i', $id);
    $getUser->execute();
    $row = $getUser->get_result()->fetch_assoc();
    if ($row && $row['user_id']) {
        $conn->query("DELETE FROM users WHERE id = " . $row['user_id']);
    }
    $stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['ok' => true]);
}

// ── ABSEN ──────────────────────────────────────────────
else if ($action === 'absen') {
    $body      = json_decode(file_get_contents('php://input'), true);
    $siswa_id  = intval($body['siswa_id']);
    $status    = $body['status']     ?? 'hadir';
    $keterangan= $body['keterangan'] ?? '';
    $lat       = floatval($body['lat'] ?? 0);
    $lng       = floatval($body['lng'] ?? 0);
    $alamat    = $body['alamat'] ?? '';
    $tanggal   = date('Y-m-d');
    $jam       = date('H:i:s');

    // Validasi status
    if (!in_array($status, ['hadir','izin','sakit'])) {
        echo json_encode(['ok' => false, 'msg' => 'Status tidak valid']); return;
    }

    // Keterangan wajib kalau izin/sakit
    if (($status === 'izin' || $status === 'sakit') && !$keterangan) {
        echo json_encode(['ok' => false, 'msg' => 'Keterangan wajib diisi untuk izin/sakit!']); return;
    }

    // Cek sudah absen hari ini?
    $cek = $conn->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
    $cek->bind_param('is', $siswa_id, $tanggal);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        echo json_encode(['ok' => false, 'msg' => 'Kamu sudah absen hari ini!']); return;
    }

    $stmt = $conn->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_absen, status, keterangan, latitude, longitude, alamat) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('issssdds', $siswa_id, $tanggal, $jam, $status, $keterangan, $lat, $lng, $alamat);

    if ($stmt->execute()) {
        $q = $conn->prepare("SELECT nama, kelas FROM siswa WHERE id = ?");
        $q->bind_param('i', $siswa_id);
        $q->execute();
        $siswa = $q->get_result()->fetch_assoc();
        echo json_encode(['ok' => true, 'data' => [
            'nama'       => $siswa['nama'],
            'kelas'      => $siswa['kelas'],
            'tanggal'    => date('d/m/Y'),
            'jam'        => substr($jam, 0, 5),
            'status'     => $status,
            'keterangan' => $keterangan,
            'alamat'     => $alamat ?: ($lat ? "$lat, $lng" : '-'),
        ]]);
    } else {
        echo json_encode(['ok' => false, 'msg' => $conn->error]);
    }
}

// ── REKAP (harian/mingguan/bulanan) ───────────────────
else if ($action === 'rekap') {
    $mode  = $_GET['mode']  ?? 'harian';
    $param = $_GET['param'] ?? date('Y-m-d');
    $kelas = $_GET['kelas'] ?? '';

    // Tentukan range tanggal berdasarkan mode
    if ($mode === 'harian') {
        $tglMulai = $param;
        $tglAkhir = $param;
    } else if ($mode === 'mingguan') {
        // param = tanggal dalam minggu itu, cari Senin-Minggu
        $ts = strtotime($param);
        $senin  = date('Y-m-d', strtotime('monday this week', $ts));
        $minggu = date('Y-m-d', strtotime('sunday this week', $ts));
        $tglMulai = $senin;
        $tglAkhir = $minggu;
    } else { // bulanan
        // param = YYYY-MM
        $tglMulai = $param . '-01';
        $tglAkhir = date('Y-m-t', strtotime($tglMulai));
    }

    // Query rekap dengan join
    $kelasWhere = $kelas ? "AND s.kelas = '$kelas'" : '';

    $sql = "SELECT s.nama, s.nis, s.kelas,
                   a.tanggal, a.jam_absen, a.status, a.keterangan, a.alamat, a.latitude, a.longitude
            FROM siswa s
            LEFT JOIN absensi a ON s.id = a.siswa_id
                AND a.tanggal BETWEEN '$tglMulai' AND '$tglAkhir'
            WHERE 1=1 $kelasWhere
            ORDER BY s.kelas, s.nama, a.tanggal";

    $result = $conn->query($sql);
    $rows   = $result->fetch_all(MYSQLI_ASSOC);

    // Hitung statistik
    $totalAbsen = count(array_filter($rows, fn($r) => $r['tanggal'] !== null));
    $hadir  = count(array_filter($rows, fn($r) => $r['status'] === 'hadir'));
    $izin   = count(array_filter($rows, fn($r) => $r['status'] === 'izin'));
    $sakit  = count(array_filter($rows, fn($r) => $r['status'] === 'sakit'));

    echo json_encode([
        'ok'     => true,
        'mode'   => $mode,
        'range'  => ['mulai' => $tglMulai, 'akhir' => $tglAkhir],
        'data'   => $rows,
        'stats'  => [
            'total_absen' => $totalAbsen,
            'hadir'  => $hadir,
            'izin'   => $izin,
            'sakit'  => $sakit,
        ]
    ]);
}
