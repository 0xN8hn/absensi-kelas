// ============================================
// LOGIN
// ============================================

let roleAktif = 'siswa';

// Ganti tab siswa / admin
function switchRole(role) {
    roleAktif = role;

    document.getElementById('tabSiswa').classList.toggle('active', role === 'siswa');
    document.getElementById('tabAdmin').classList.toggle('active', role === 'admin');

    if (role === 'admin') {
        document.getElementById('loginTitle').textContent    = 'Halo, Admin!';
        document.getElementById('loginSubtitle').textContent = 'Masuk untuk kelola data dan rekap absensi.';
    } else {
        document.getElementById('loginTitle').textContent    = 'Halo, Siswa!';
        document.getElementById('loginSubtitle').textContent = 'Masuk untuk melakukan absensi hari ini.';
    }

    // Clear form
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('alertErr').classList.remove('show');
}

// Proses login
async function doLogin() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('alertErr');

    alertBox.classList.remove('show');

    if (!username || !password) {
        alertBox.textContent = 'Username dan password wajib diisi!';
        alertBox.classList.add('show');
        return;
    }

    // Kirim ke API
    const response = await fetch('api.php?action=login', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    const hasil = await response.json();

    if (hasil.ok) {
        // Cek role sesuai tab yang dipilih
        if (hasil.role !== roleAktif) {
            alertBox.textContent = hasil.role === 'admin'
                ? 'Akun ini adalah akun Admin. Pilih tab Admin.'
                : 'Akun ini adalah akun Siswa. Pilih tab Siswa.';
            alertBox.classList.add('show');
            return;
        }

        // Simpan data login ke sessionStorage
        sessionStorage.setItem('role',    hasil.role);
        sessionStorage.setItem('user_id', hasil.user_id);
        if (hasil.siswa) {
            sessionStorage.setItem('siswa', JSON.stringify(hasil.siswa));
        }

        // Redirect sesuai role
        if (hasil.role === 'admin') {
            window.location.href = 'admin.php';
        } else {
            window.location.href = 'index.php';
        }
    } else {
        alertBox.textContent = hasil.msg;
        alertBox.classList.add('show');
    }
}

// Enter untuk login
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doLogin();
});
