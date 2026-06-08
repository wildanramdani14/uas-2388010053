<?php
require_once __DIR__ . '/../src/auth.php';
$user = requireLogin();
require_once __DIR__ . '/../src/db.php';

$totalBuku       = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$totalPeminjaman = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'")->fetchColumn();
$totalDikembalikan = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='dikembalikan'")->fetchColumn();
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$recentPinjam = $pdo->query("
  SELECT p.*, b.judul FROM peminjaman p
  JOIN buku b ON p.buku_id = b.id
  ORDER BY p.id DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../src/layout_top.php';
?>

<div class="page-header">
  <h1>Menu Dashboard</h1>
  <p>Selamat datang Kakang Arip, <?= htmlspecialchars($user['nama']) ?>!</p>
</div>

<div class="stats">
  <div class="stat">
    <div class="stat-label">Total Buku</div>
    <div class="stat-val"><?= $totalBuku ?></div>
  </div>
  <div class="stat">
    <div class="stat-label">Sedang Dipinjam</div>
    <div class="stat-val" style="color:#2563eb"><?= $totalPeminjaman ?></div>
  </div>
  <div class="stat">
    <div class="stat-label">Dikembalikan</div>
    <div class="stat-val" style="color:#15803d"><?= $totalDikembalikan ?></div>
  </div>
  <div class="stat">
    <div class="stat-label">Pengguna Sistem</div>
    <div class="stat-val"><?= $totalUsers ?></div>
  </div>
</div>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <h2 style="font-size:15px;font-weight:600;color:#0f172a;">Peminjaman Terbaru</h2>
    <a href="peminjaman.php" class="btn btn-primary btn-sm">Lihat Semua</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Judul Buku</th>
        <th>Nama Peminjam</th>
        <th>Tanggal Pinjam</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentPinjam as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['judul']) ?></td>
        <td><?= htmlspecialchars($row['nama_peminjam']) ?></td>
        <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
        <td>
          <?php if ($row['status'] === 'dipinjam'): ?>
            <span class="badge badge-blue">Dipinjam</span>
          <?php else: ?>
            <span class="badge badge-green">Dikembalikan</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../src/layout_bottom.php'; ?>
