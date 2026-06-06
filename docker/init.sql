CREATE DATABASE IF NOT EXISTS perpustakaan;
USE perpustakaan;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','petugas') DEFAULT 'petugas',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  pengarang VARCHAR(100) NOT NULL,
  penerbit VARCHAR(100),
  tahun INT,
  stok INT DEFAULT 1,
  kategori VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buku_id INT NOT NULL,
  nama_peminjam VARCHAR(100) NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_kembali DATE,
  status ENUM('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- Seed users (password: admin123 dan petugas123 - hashed bcrypt)
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Petugas Satu', 'petugas', '$2y$10$TKh8H1.PfbuNIVpzl1l6zuFrMjugg5rHqG3VJqnFk1MvnPpqyG2iy', 'petugas');

-- Seed buku
INSERT INTO buku (judul, pengarang, penerbit, tahun, stok, kategori) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 3, 'Novel'),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 2, 'Novel'),
('Pemrograman Web dengan PHP', 'Abdul Kadir', 'Andi Publisher', 2018, 5, 'Teknologi'),
('Jaringan Komputer', 'Andrew Tanenbaum', 'Pearson', 2011, 4, 'Teknologi'),
('Matematika Diskrit', 'Kenneth Rosen', 'McGraw-Hill', 2012, 3, 'Sains'),
('Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 4, 'Pengembangan Diri'),
('Atomic Habits', 'James Clear', 'Penguin Random House', 2018, 3, 'Pengembangan Diri'),
('Harry Potter and The Sorcerer Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 2, 'Novel');

-- Seed peminjaman
INSERT INTO peminjaman (buku_id, nama_peminjam, tanggal_pinjam, tanggal_kembali, status) VALUES
(1, 'Budi Santoso', '2025-06-01', NULL, 'dipinjam'),
(3, 'Siti Rahayu', '2025-06-02', NULL, 'dipinjam'),
(5, 'Ahmad Fauzi', '2025-05-20', '2025-05-27', 'dikembalikan');
