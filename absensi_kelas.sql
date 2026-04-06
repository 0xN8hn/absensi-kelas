CREATE DATABASE IF NOT EXISTS absensi_kelas CHARACTER SET utf8mb4;
USE absensi_kelas;

-- Tabel users (login siswa & admin)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','siswa') NOT NULL DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel siswa
CREATE TABLE IF NOT EXISTS siswa (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT,
    nama       VARCHAR(100) NOT NULL,
    kelas      VARCHAR(30)  NOT NULL,
    nis        VARCHAR(20)  NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel absensi
CREATE TABLE IF NOT EXISTS absensi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id    INT NOT NULL,
    tanggal     DATE NOT NULL,
    jam_absen   TIME NOT NULL,
    status      ENUM('hadir','izin','sakit') NOT NULL DEFAULT 'hadir',
    keterangan  TEXT,
    latitude    DECIMAL(10,8) DEFAULT 0,
    longitude   DECIMAL(11,8) DEFAULT 0,
    alamat      TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    UNIQUE KEY unique_per_hari (siswa_id, tanggal)
);

-- Default admin account (password: admin123)
INSERT IGNORE INTO users (username, password, role) VALUES
('admin', 'admin123', 'admin');
