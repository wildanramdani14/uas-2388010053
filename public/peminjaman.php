<?php
require_once __DIR__ . '/../src/auth.php';
$user = requireLogin();
require_once __DIR__ . '/../src/db.php';

$msg = '';
$msgType = '';

// Tambah peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pinjam') {
    $bukuId = intval($_POST['buku_id']);
    // cek stok
    $stok = $pdo->prepare("SELECT stok FROM buku WHERE id=?");
    $stok->execute([$bukuId]);
    $stokVal = $stok->fetchColumn();
    if ($stokVal > 0) {
        $stmt = $pdo->prepare("INSERT INTO peminjaman (buku_id, nama_peminjam, tanggal_pinjam, status) VALUES (?,?,?,?)");
        $stmt->execute([$bukuId, trim($_POST['nama_peminjam']), $_POST['tanggal_pinjam'], 'dipinjam']);
        $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id=?")->execute([$bukuId]);
        $msg = 'Peminjaman berhasil dicatat.';
        $msgType = 'success';
    } else {
        $msg = 'Stok buku habis, tidak dapat dipinjam.';
        $msgType = 'error';
    }
}

// Kembalikan buku
if (isset($_GET['kembali'])) {
    $pid = intval($_GET['kembali']);
    $row = $pdo->prepare("SELECT * FROM peminjaman WHERE id=?");
    $row->execute([$pid]);
    $pinjam = $row->fetch();
    if ($pinjam && $pinjam['status'] === 'dipinjam') {
        $pdo->prepare("UPDATE peminjaman SET status='dikembalikan', tanggal_kembali=CURDATE() WHERE id=?")->execute([$pid]);
        $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id=?")->execute([$pinjam['buku_id']]);
        $msg = 'Buku berhasil dikembalikan.';
        $msgType = 'success';
    }
}

// Hapus
if (isset($_GET['hapus']) && isAdmin()) {
    $pdo->prepare("DELETE FROM peminjaman WHERE id=?")->execute([intval($_GET['hapus'])]);
    $msg = 'Data peminjaman dihapus.';
    $msgType = 'success';
}

$filter = $_GET['filter'] ?? 'semua';
if ($filter === 'dipinjam') {
    $pemList = $pdo->query("SELECT p.*, b.judul FROM peminjaman p JOIN buku b ON p.buku_id=b.id WHERE p.status='dipinjam' ORDER BY p.id DESC")->fetchAll();
} elseif ($filter === 'dikembalikan') {
    $pemList = $pdo->query("SELECT p.*, b.judul FROM peminjaman p JOIN buku b ON p.buku_id=b.id WHERE p.status='dikembalikan' ORDER BY p.id DESC")->fetchAll();
} else {
    $pemList = $pdo->query("SELECT p.*, b.judul FROM peminjaman p JOIN buku b ON p.buku_id=b.id ORDER BY p.id DESC")->fetchAll();
}

$bukuList = $pdo->query("SELECT id, judul, stok FROM buku ORDER BY judul")->fetchAll();

$pageTitle = 'Peminjaman';
require_once __DIR__ . '/../src/layout_top.php';
?>

<div class="page-header">
  <h1>Peminjaman Buku</h1>
  <p>Catat dan kelola peminjaman buku perpustakaan</p>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Form Pinjam -->
<div class="card" style="margin-bottom:1.5rem;">
  <h2 style="font-size:15px;font-weight:600;margin-bottom:1rem;color:#0f172a;">Catat Peminjaman Baru</h2>
  <form method="POST">
    <input type="hidden" name="action" value="pinjam"/>
    <div class="form-row">
      <div class="form-group">
        <label>Nama Peminjam</label>
        <input type="text" name="nama_peminjam" required placeholder="Nama lengkap peminjam"/>
      </div>
      <div class="form-group">
        <label>Tanggal Pinjam</label>
        <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" required/>
      </div>
    </div>
    <div class="form-group">
      <label>Pilih Buku</label>
      <select name="buku_id" required>
        <option value="">-- Pilih buku --</option>
        <?php foreach ($bukuList as $b): ?>
        <option value="<?= $b['id'] ?>" <?= $b['stok'] <= 0 ? 'disabled' : '' ?>>
          <?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok'] ?>)
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Catat Peminjaman</button>
  </form>
</div>

<!-- Tabel Peminjaman -->
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:8px;">
    <h2 style="font-size:15px;font-weight:600;color:#0f172a;">Riwayat Peminjaman</h2>
    <div style="display:flex;gap:6px;">
      <a href="?filter=semua" class="btn btn-sm <?= $filter==='semua' ? 'btn-primary' : '' ?>" style="<?= $filter!=='semua' ? 'background:#f1f5f9;color:#475569;' : '' ?>">Semua</a>
      <a href="?filter=dipinjam" class="btn btn-sm <?= $filter==='dipinjam' ? 'btn-primary' : '' ?>" style="<?= $filter!=='dipinjam' ? 'background:#f1f5f9;color:#475569;' : '' ?>">Dipinjam</a>
      <a href="?filter=dikembalikan" class="btn btn-sm <?= $filter==='dikembalikan' ? 'btn-primary' : '' ?>" style="<?= $filter!=='dikembalikan' ? 'background:#f1f5f9;color:#475569;' : '' ?>">Dikembalikan</a>
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Judul Buku</th>
        <th>Nama Peminjam</th>
        <th>Tgl Pinjam</th>
        <th>Tgl Kembali</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pemList as $i => $p): ?>
      <tr>
        <td style="color:#94a3b8;"><?= $i+1 ?></td>
        <td style="font-weight:500;"><?= htmlspecialchars($p['judul']) ?></td>
        <td><?= htmlspecialchars($p['nama_peminjam']) ?></td>
        <td><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
        <td><?= $p['tanggal_kembali'] ? date('d M Y', strtotime($p['tanggal_kembali'])) : '<span style="color:#94a3b8;">–</span>' ?></td>
        <td>
          <?php if ($p['status'] === 'dipinjam'): ?>
            <span class="badge badge-blue">Dipinjam</span>
          <?php else: ?>
            <span class="badge badge-green">Dikembalikan</span>
          <?php endif; ?>
        </td>
        <td style="display:flex;gap:6px;">
          <?php if ($p['status'] === 'dipinjam'): ?>
          <a href="?kembali=<?= $p['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi pengembalian buku?')">Kembalikan</a>
          <?php endif; ?>
          <?php if (isAdmin()): ?>
          <a href="?hapus=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pemList)): ?>
      <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada data peminjaman.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../src/layout_bottom.php'; ?>
