// ============================================
// ADMIN PANEL
// ============================================

// Cek login admin
if (sessionStorage.getItem('role') !== 'admin') {
    window.location.href = 'login.php';
}

let semuaSiswa = [];
let modeRekap  = 'harian';

// ── NAVIGASI HALAMAN ────────────────────────
function showPage(nama) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('show'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('page-' + nama).classList.add('show');
    document.getElementById('nav-' + nama).classList.add('active');
}

// ── KELOLA SISWA ────────────────────────────
async function loadSiswa() {
    const res  = await fetch('api.php?action=get_semua_siswa');
    const json = await res.json();
    semuaSiswa = json.data || [];
    renderSiswa(semuaSiswa);
    loadKelasFilter();
}

function renderSiswa(list) {
    const el = document.getElementById('siswaList');
    document.getElementById('siswaCount').textContent = list.length + ' siswa';

    if (!list.length) {
        el.innerHTML = '<div class="empty-state">Belum ada siswa. Tambahkan di form kiri.</div>';
        return;
    }

    const perKelas = {};
    list.forEach(s => {
        if (!perKelas[s.kelas]) perKelas[s.kelas] = [];
        perKelas[s.kelas].push(s);
    });

    let html = '';
    Object.keys(perKelas).sort().forEach(kelas => {
        html += '<div class="kelas-header">' + esc(kelas) + ' · ' + perKelas[kelas].length + ' siswa</div>';
        perKelas[kelas].forEach(s => {
            html += '<div class="siswa-row">' +
                '<div>' +
                    '<div class="siswa-name">' + esc(s.nama) + '</div>' +
                    '<div class="siswa-meta">NIS: ' + esc(s.nis) + ' · Login: ' + esc(s.username || '-') + '</div>' +
                '</div>' +
                '<button class="btn btn-danger" onclick="hapusSiswa(' + s.id + ',\'' + esc(s.nama) + '\')">Hapus</button>' +
            '</div>';
        });
    });

    el.innerHTML = html;
}

function filterSiswa() {
    const q = document.getElementById('searchSiswa').value.toLowerCase();
    if (!q) { renderSiswa(semuaSiswa); return; }
    renderSiswa(semuaSiswa.filter(s =>
        s.nama.toLowerCase().includes(q) ||
        s.kelas.toLowerCase().includes(q) ||
        s.nis.includes(q)
    ));
}

async function tambahSiswa() {
    const nama     = document.getElementById('inpNama').value.trim();
    const kelas    = document.getElementById('inpKelas').value.trim();
    const nis      = document.getElementById('inpNis').value.trim();
    const username = document.getElementById('inpUsername').value.trim();
    const password = document.getElementById('inpPassword').value.trim();
    const btn      = document.getElementById('btnTambah');
    const aOk      = document.getElementById('alertOk');
    const aErr     = document.getElementById('alertErr');

    aOk.classList.remove('show');
    aErr.classList.remove('show');

    if (!nama || !kelas || !nis || !username || !password) {
        aErr.textContent = '⚠ Semua kolom wajib diisi!';
        aErr.classList.add('show');
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner"></span>Menyimpan...';

    const res  = await fetch('api.php?action=tambah_siswa', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nama, kelas, nis, username, password })
    });
    const json = await res.json();

    btn.disabled    = false;
    btn.textContent = 'Tambah Siswa';

    if (json.ok) {
        aOk.textContent = '✓ ' + json.msg;
        aOk.classList.add('show');
        ['inpNama','inpNis','inpUsername','inpPassword'].forEach(id => {
            document.getElementById(id).value = '';
        });
        loadSiswa();
    } else {
        aErr.textContent = '⚠ ' + json.msg;
        aErr.classList.add('show');
    }
}

async function hapusSiswa(id, nama) {
    if (!confirm('Hapus siswa "' + nama + '"?\nAkun login dan data absensinya juga akan terhapus.')) return;
    const fd = new FormData();
    fd.append('id', id);
    const res  = await fetch('api.php?action=hapus_siswa', { method:'POST', body:fd });
    const json = await res.json();
    if (json.ok) loadSiswa();
    else alert('Gagal: ' + json.msg);
}

// ── REKAP ────────────────────────────────────
async function loadKelasFilter() {
    const res  = await fetch('api.php?action=get_kelas');
    const json = await res.json();
    const sel  = document.getElementById('fKelas');
    sel.innerHTML = '<option value="">Semua Kelas</option>';
    json.data.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k; opt.textContent = k;
        sel.appendChild(opt);
    });
}

// Ganti mode harian/mingguan/bulanan
function switchMode(mode) {
    modeRekap = mode;

    ['harian','mingguan','bulanan'].forEach(m => {
        document.getElementById('tab-' + m).classList.toggle('active', m === mode);
        const filterId = 'filter' + m.charAt(0).toUpperCase() + m.slice(1);
        document.getElementById(filterId).style.display = m === mode ? 'block' : 'none';
    });
}

async function loadRekap() {
    const kelas = document.getElementById('fKelas').value;
    let param   = '';

    if (modeRekap === 'harian') {
        param = document.getElementById('fTgl').value;
        if (!param) { alert('Pilih tanggal dulu!'); return; }
    } else if (modeRekap === 'mingguan') {
        param = document.getElementById('fMinggu').value;
        if (!param) { alert('Pilih tanggal dalam minggu!'); return; }
    } else {
        param = document.getElementById('fBulan').value;
        if (!param) { alert('Pilih bulan dulu!'); return; }
    }

    let url = 'api.php?action=rekap&mode=' + modeRekap + '&param=' + param;
    if (kelas) url += '&kelas=' + encodeURIComponent(kelas);

    const res  = await fetch(url);
    const json = await res.json();
    if (!json.ok) return;

    const { data, stats, range } = json;

    // Update stats
    document.getElementById('stTotal').textContent = stats.total_absen;
    document.getElementById('stHadir').textContent = stats.hadir;
    document.getElementById('stIzin').textContent  = stats.izin;
    document.getElementById('stSakit').textContent = stats.sakit;

    const pct = stats.total_absen > 0 ? Math.round(stats.hadir / stats.total_absen * 100) : 0;
    document.getElementById('stPct').textContent = pct + '% hadir';

    // Update subtitle
    const fmtTgl = t => new Date(t + 'T00:00:00').toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
    const subtitle = modeRekap === 'harian'
        ? fmtTgl(range.mulai)
        : modeRekap === 'mingguan'
        ? fmtTgl(range.mulai) + ' — ' + fmtTgl(range.akhir)
        : new Date(range.mulai + 'T00:00:00').toLocaleDateString('id-ID', { month:'long', year:'numeric' });
    document.getElementById('rekapSubtitle').textContent = subtitle;

    // Render tabel
    document.getElementById('rekapCount').textContent = data.length + ' data';

    if (!data.length) {
        document.getElementById('rekapBody').innerHTML =
            '<tr><td colspan="8" class="empty-state">Tidak ada data pada periode ini.</td></tr>';
        return;
    }

    const badgeMap = {
        hadir: '<span class="badge badge-hadir">✓ Hadir</span>',
        izin:  '<span class="badge badge-izin">📝 Izin</span>',
        sakit: '<span class="badge badge-sakit">🤒 Sakit</span>',
    };

    let html = '';
    data.forEach((r, i) => {
        const tgl  = r.tanggal ? new Date(r.tanggal + 'T00:00:00').toLocaleDateString('id-ID', { day:'2-digit', month:'short' }) : '—';
        const jam  = r.jam_absen ? r.jam_absen.slice(0,5) : '—';
        const lok  = r.alamat || (r.latitude ? r.latitude + ',' + r.longitude : '—');
        const badge = r.status ? (badgeMap[r.status] || r.status) : '<span class="badge badge-tidak">✗ Absen</span>';

        html += '<tr>' +
            '<td class="td-no">' + (i+1) + '</td>' +
            '<td>' + esc(r.nama) + '<span class="td-sub">NIS: ' + esc(r.nis) + '</span></td>' +
            '<td class="td-sub">' + esc(r.kelas) + '</td>' +
            '<td style="font-family:var(--mono);font-size:12px">' + tgl + '</td>' +
            '<td class="td-jam">' + jam + '</td>' +
            '<td>' + badge + '</td>' +
            '<td style="font-size:12px;color:var(--muted);max-width:150px">' + esc(r.keterangan || '—') + '</td>' +
            '<td class="td-loc" title="' + esc(lok) + '">' + esc(lok) + '</td>' +
        '</tr>';
    });

    document.getElementById('rekapBody').innerHTML = html;
}

function logout() {
    sessionStorage.clear();
}

function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── EVENT LISTENERS ─────────────────────────
document.getElementById('btnTambah').addEventListener('click', tambahSiswa);
document.getElementById('btnRekap').addEventListener('click',  loadRekap);
document.getElementById('searchSiswa').addEventListener('input', filterSiswa);

// ── INIT ────────────────────────────────────
document.getElementById('fTgl').value   = new Date().toISOString().split('T')[0];
document.getElementById('fMinggu').value = new Date().toISOString().split('T')[0];
document.getElementById('fBulan').value = new Date().toISOString().slice(0,7);
loadSiswa();
