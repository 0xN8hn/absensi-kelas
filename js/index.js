// ============================================
// HALAMAN ABSEN SISWA
// ============================================

// Cek login — kalau belum login, balik ke login page
if (!sessionStorage.getItem('role') || sessionStorage.getItem('role') !== 'siswa') {
    window.location.href = 'login.php';
}

// Variabel state
let dataGPS    = null;
let alamat     = '';
let statusAbsen = '';

// Tampilkan nama user yang login
const dataSiswa = JSON.parse(sessionStorage.getItem('siswa') || '{}');
document.getElementById('namaUser').textContent = dataSiswa.nama || 'Siswa';

// Kalau siswa sudah punya data kelas, auto-isi step 1
if (dataSiswa.kelas) {
    const selKelas = document.getElementById('selKelas');
    // Akan diisi setelah loadKelas()
}

// ── JAM & TANGGAL ──────────────────────────
function updateJam() {
    const d    = new Date();
    const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    document.getElementById('clock').textContent = d.toLocaleTimeString('id-ID');
    document.getElementById('tglDisplay').textContent =
        hari[d.getDay()] + ', ' + d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
}
setInterval(updateJam, 1000);
updateJam();

// ── NAVIGASI STEP ──────────────────────────
// Total 5 step sekarang (kelas, nama, status, lokasi, konfirmasi)
function goToStep(n) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('show'));
    const panels = { 1:'p1', 2:'p2', 3:'p3', 4:'p4', 5:'p5', 6:'pSukses' };
    document.getElementById(panels[n]).classList.add('show');

    for (let i = 1; i <= 5; i++) {
        const c = document.getElementById('sc' + i);
        if (i < n) {
            c.className = 'step-circle done';
            c.textContent = '✓';
        } else if (i === n) {
            c.className = 'step-circle active';
            c.textContent = i;
        } else {
            c.className = 'step-circle';
            c.textContent = i;
        }
    }

    for (let i = 1; i <= 4; i++) {
        document.getElementById('sl' + i).className = 'step-line' + (i < n ? ' done' : '');
    }

    if (n === 4) ambilGPS();
    if (n === 5) tampilkanKonfirmasi();
}

// ── LOAD DATA ──────────────────────────────
async function loadKelas() {
    const res  = await fetch('api.php?action=get_kelas');
    const json = await res.json();
    const sel  = document.getElementById('selKelas');
    sel.innerHTML = '<option value="">— Pilih kelas —</option>';
    json.data.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k;
        opt.textContent = k;
        sel.appendChild(opt);
    });

    // Auto-pilih kelas siswa yang login
    if (dataSiswa.kelas) {
        sel.value = dataSiswa.kelas;
        document.getElementById('btnKelas').disabled = false;
        await loadSiswa(dataSiswa.kelas);
        // Auto-pilih nama
        if (dataSiswa.id) {
            document.getElementById('selSiswa').value = dataSiswa.id;
            document.getElementById('btnSiswa').disabled = false;
        }
    }
}

async function onPilihKelas() {
    const kelas = document.getElementById('selKelas').value;
    document.getElementById('btnKelas').disabled = !kelas;
    if (kelas) await loadSiswa(kelas);
}

async function loadSiswa(kelas) {
    const res  = await fetch('api.php?action=get_siswa&kelas=' + encodeURIComponent(kelas));
    const json = await res.json();
    const sel  = document.getElementById('selSiswa');
    sel.innerHTML = '<option value="">— Pilih nama —</option>';
    json.data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.nama + ' · ' + s.nis;
        sel.appendChild(opt);
    });
    document.getElementById('btnSiswa').disabled = true;
}

// ── PILIH STATUS ────────────────────────────
function pilihStatus(status) {
    statusAbsen = status;

    // Reset semua tombol status
    ['hadir','izin','sakit'].forEach(s => {
        document.getElementById('btn' + s.charAt(0).toUpperCase() + s.slice(1))
            .className = 'status-btn';
    });

    // Highlight yang dipilih
    const idMap = { hadir: 'btnHadir', izin: 'btnIzin', sakit: 'btnSakit' };
    document.getElementById(idMap[status]).className = 'status-btn selected-' + status;

    // Tampilkan/sembunyikan keterangan
    const kotakKeterangan = document.getElementById('keteranganBox');
    const labelKet        = document.getElementById('keteranganLabel');

    if (status === 'izin' || status === 'sakit') {
        kotakKeterangan.classList.add('show');
        labelKet.innerHTML = 'Keterangan ' + (status === 'izin' ? 'Izin' : 'Sakit') +
            ' <span style="color:var(--red)">*</span>';
        document.getElementById('btnStatus').disabled = false;
    } else {
        kotakKeterangan.classList.remove('show');
        document.getElementById('inpKeterangan').value = '';
        document.getElementById('btnStatus').disabled = false;
    }
}

// Validasi keterangan sebelum lanjut dari step status
function validasiStatus() {
    if (!statusAbsen) return;

    if ((statusAbsen === 'izin' || statusAbsen === 'sakit')) {
        const keterangan = document.getElementById('inpKeterangan').value.trim();
        if (!keterangan) {
            document.getElementById('inpKeterangan').focus();
            document.getElementById('inpKeterangan').style.borderColor = 'var(--red)';
            setTimeout(() => {
                document.getElementById('inpKeterangan').style.borderColor = '';
            }, 2000);
            return;
        }
    }

    goToStep(4);
}

// ── AMBIL GPS ───────────────────────────────
function ambilGPS() {
    dataGPS = null;
    alamat  = '';

    const box   = document.getElementById('locBox');
    const icon  = document.getElementById('locIcon');
    const text  = document.getElementById('locText');
    const pulse = document.getElementById('locPulse');
    const btn   = document.getElementById('btnLokasi');

    box.className    = 'loc-box';
    text.className   = 'loc-text';
    text.textContent = 'Mengambil lokasi GPS...';
    icon.textContent = '📍';
    pulse.style.display = 'block';
    btn.disabled = true;

    if (!navigator.geolocation) {
        text.textContent = 'Browser tidak support GPS.';
        text.className   = 'loc-text err';
        box.className    = 'loc-box err';
        pulse.style.display = 'none';
        // Boleh lanjut tanpa GPS
        btn.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async pos => {
            dataGPS = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            pulse.style.display = 'none';
            alamat = dataGPS.lat.toFixed(6) + ', ' + dataGPS.lng.toFixed(6);
            text.className   = 'loc-text ok';
            text.textContent = '📌 ' + alamat;
            icon.textContent = '🟢';
            box.className    = 'loc-box ok';
            btn.disabled     = false;
        },
        err => {
            pulse.style.display = 'none';
            icon.textContent    = '⚠️';
            text.className      = 'loc-text err';
            text.textContent    = err.code === 1
                ? 'Izin lokasi ditolak. Lanjut tanpa GPS.'
                : 'Gagal ambil GPS. Lanjut tanpa GPS.';
            box.className  = 'loc-box err';
            btn.disabled   = false; // Boleh lanjut meski tanpa GPS
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// ── KONFIRMASI ──────────────────────────────
function tampilkanKonfirmasi() {
    const pilihanSiswa = document.getElementById('selSiswa').selectedOptions[0];
    const nama  = pilihanSiswa ? pilihanSiswa.text.split('·')[0].trim() : '—';
    const kelas = document.getElementById('selKelas').value;
    const ket   = document.getElementById('inpKeterangan').value.trim();
    const d     = new Date();

    const emojiStatus = { hadir: '✅', izin: '📝', sakit: '🤒' };
    const labelStatus = { hadir: 'Hadir', izin: 'Izin', sakit: 'Sakit' };

    let html = baris('Nama', nama) +
               baris('Kelas', kelas) +
               baris('Tanggal', d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' })) +
               baris('Jam', d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' })) +
               baris('Status', (emojiStatus[statusAbsen] || '') + ' ' + (labelStatus[statusAbsen] || '-'));

    if (ket) html += baris('Keterangan', ket);
    html += baris('Lokasi', dataGPS ? alamat : 'Tidak ada GPS');

    document.getElementById('confirmGrid').innerHTML = html;
    document.getElementById('alertAbsen').classList.remove('show');
}

function baris(key, val) {
    return '<div class="info-row">' +
        '<span class="info-key">' + key + '</span>' +
        '<span class="info-val">' + escHtml(val) + '</span>' +
    '</div>';
}

// ── SUBMIT ──────────────────────────────────
async function submitAbsen() {
    const siswaId   = document.getElementById('selSiswa').value;
    const keterangan = document.getElementById('inpKeterangan').value.trim();
    const btn       = document.getElementById('btnAbsen');
    const alertBox  = document.getElementById('alertAbsen');

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner"></span>Mengirim...';
    alertBox.classList.remove('show');

    const response = await fetch('api.php?action=absen', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            siswa_id:   siswaId,
            status:     statusAbsen,
            keterangan: keterangan,
            lat:        dataGPS ? dataGPS.lat : 0,
            lng:        dataGPS ? dataGPS.lng : 0,
            alamat:     alamat
        })
    });

    const hasil = await response.json();

    if (hasil.ok) {
        const d = hasil.data;
        const emojiStatus = { hadir: '✅', izin: '📝', sakit: '🤒' };
        document.getElementById('resultGrid').innerHTML =
            baris('Nama',       d.nama) +
            baris('Kelas',      d.kelas) +
            baris('Tanggal',    d.tanggal) +
            baris('Jam',        d.jam) +
            baris('Status',     (emojiStatus[d.status] || '') + ' ' + d.status) +
            (d.keterangan ? baris('Keterangan', d.keterangan) : '') +
            baris('Lokasi',     d.alamat);
        goToStep(6);
    } else {
        alertBox.textContent = '⚠ ' + hasil.msg;
        alertBox.classList.add('show');
        btn.disabled    = false;
        btn.textContent = '✓ Kirim Absensi';
    }
}

// ── RESET ───────────────────────────────────
function resetForm() {
    dataGPS      = null;
    alamat       = '';
    statusAbsen  = '';
    document.getElementById('selKelas').value   = '';
    document.getElementById('selSiswa').innerHTML = '<option value="">— Pilih nama —</option>';
    document.getElementById('btnKelas').disabled = true;
    document.getElementById('inpKeterangan').value = '';
    document.getElementById('keteranganBox').classList.remove('show');
    ['btnHadir','btnIzin','btnSakit'].forEach(id => {
        document.getElementById(id).className = 'status-btn';
    });
    goToStep(1);
}

function logout() {
    sessionStorage.clear();
    window.location.href = 'login.php';
}

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── EVENT LISTENERS ─────────────────────────
document.getElementById('selKelas').addEventListener('change', onPilihKelas);
document.getElementById('selSiswa').addEventListener('change', function() {
    document.getElementById('btnSiswa').disabled = !this.value;
});

document.getElementById('btnKelas').addEventListener('click',  () => goToStep(2));
document.getElementById('btnSiswa').addEventListener('click',  () => goToStep(3));
document.getElementById('btnStatus').addEventListener('click', validasiStatus);
document.getElementById('btnLokasi').addEventListener('click', () => goToStep(5));
document.getElementById('btnAbsen').addEventListener('click',  submitAbsen);
document.getElementById('btnUlang').addEventListener('click',  resetForm);
document.getElementById('btnBack1').addEventListener('click',  () => goToStep(1));
document.getElementById('btnBack2').addEventListener('click',  () => goToStep(2));
document.getElementById('btnBack3').addEventListener('click',  () => goToStep(3));
document.getElementById('btnBack4').addEventListener('click',  () => goToStep(4));

// ── INIT ────────────────────────────────────
loadKelas();
