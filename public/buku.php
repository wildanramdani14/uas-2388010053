<?php
require_once __DIR__ . '/../src/auth.php';
$user = requireLogin();
require_once __DIR__ . '/../src/db.php';

$msg = '';
$msgType = '';

// Tambah buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $stmt = $pdo->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun, stok, kategori) VALUES (?,?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['judul']),
        trim($_POST['pengarang']),
        trim($_POST['penerbit']),
        intval($_POST['tahun']),
        intval($_POST['stok']),
        trim($_POST['kategori']),
    ]);
    $msg = 'Buku berhasil ditambahkan.';
    $msgType = 'success';
}

// Edit buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $stmt = $pdo->prepare("UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun=?, stok=?, kategori=? WHERE id=?");
    $stmt->execute([
        trim($_POST['judul']),
        trim($_POST['pengarang']),
        trim($_POST['penerbit']),
        intval($_POST['tahun']),
        intval($_POST['stok']),
        trim($_POST['kategori']),
        intval($_POST['id']),
    ]);
    $msg = 'Buku berhasil diperbarui.';
    $msgType = 'success';
}

// Hapus buku
if (isset($_GET['hapus']) && isAdmin()) {
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id=?");
    $stmt->execute([intval($_GET['hapus'])]);
    $msg = 'Buku berhasil dihapus.';
    $msgType = 'success';
}

// Edit form data
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM buku WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $editData = $stmt->fetch();
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM buku WHERE judul LIKE ? OR pengarang LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM buku ORDER BY id DESC");
}
$bukuList = $stmt->fetchAll();

$kategoriList = ['Novel','Teknologi','Sains','Pengembangan Diri','Sejarah','Lainnya'];

$pageTitle = 'Data Buku';
require_once __DIR__ . '/../src/layout_top.php';
?>

<div class="page-header">
  <h1>Data Buku</h1>
  <p>Kelola koleksi buku perpustakaan</p>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Form Tambah / Edit -->
<div class="card" style="margin-bottom:1.5rem;">
  <h2 style="font-size:15px;font-weight:600;margin-bottom:1rem;color:#0f172a;">
    <?= $editData ? 'Edit Buku' : 'Tambah Buku Baru' ?>
  </h2>
  <form method="POST">
    <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'tambah' ?>"/>
    <?php if ($editData): ?>
    <input type="hidden" name="id" value="<?= $editData['id'] ?>"/>
    <?php endif; ?>
    <div class="form-row">
      <div class="form-group">
        <label>Judul Buku</label>
        <input type="text" name="judul" value="<?= htmlspecialchars($editData['judul'] ?? '') ?>" required placeholder="Masukkan judul buku"/>
      </div>
      <div class="form-group">
        <label>Pengarang</label>
        <input type="text" name="pengarang" value="<?= htmlspecialchars($editData['pengarang'] ?? '') ?>" required placeholder="Nama pengarang"/>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Penerbit</label>
        <input type="text" name="penerbit" value="<?= htmlspecialchars($editData['penerbit'] ?? '') ?>" placeholder="Nama penerbit"/>
      </div>
      <div class="form-group">
        <label>Tahun Terbit</label>
        <input type="number" name="tahun" value="<?= htmlspecialchars($editData['tahun'] ?? date('Y')) ?>" min="1900" max="<?= date('Y') ?>"/>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Stok</label>
        <input type="number" name="stok" value="<?= htmlspecialchars($editData['stok'] ?? 1) ?>" min="0"/>
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <select name="kategori">
          <?php foreach ($kategoriList as $kat): ?>
          <option value="<?= $kat ?>" <?= ($editData['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= $kat ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:8px;">
      <button type="submit" class="btn btn-primary">
        <?= $editData ? 'Simpan Pembaruan' : 'Tambah Buku' ?>
      </button>
      <?php if ($editData): ?>
      <a href="buku.php" class="btn" style="background:#f1f5f9;color:#475569;">Batal</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Tabel Buku -->
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;">
    <h2 style="font-size:15px;font-weight:600;color:#0f172a;">Daftar Buku (<?= count($bukuList) ?>)</h2>
    <form method="GET" style="display:flex;gap:8px;">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul / pengarang..." style="padding:7px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;width:220px;"/>
      <button type="submit" class="btn btn-primary btn-sm">Cari</button>
      <?php if ($search): ?><a href="buku.php" class="btn btn-sm" style="background:#f1f5f9;color:#475569;">Reset</a><?php endif; ?>
    </form>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Judul</th>
        <th>Pengarang</th>
        <th>Kategori</th>
        <th>Tahun</th>
        <th>Stok</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bukuList as $i => $b): ?>
      <tr>
        <td style="color:#94a3b8;"><?= $i+1 ?></td>
        <td style="font-weight:500;"><?= htmlspecialchars($b['judul']) ?></td>
        <td><?= htmlspecialchars($b['pengarang']) ?></td>
        <td><span class="badge badge-gray"><?= htmlspecialchars($b['kategori']) ?></span></td>
        <td><?= $b['tahun'] ?></td>
        <td>
          <?php if ($b['stok'] > 0): ?>
            <span class="badge badge-green"><?= $b['stok'] ?></span>
          <?php else: ?>
            <span class="badge badge-red">Habis</span>
          <?php endif; ?>
        </td>
        <td style="display:flex;gap:6px;">
          <a href="buku.php?edit=<?= $b['id'] ?>" class="btn btn-success btn-sm">Edit</a>
          <?php if (isAdmin()): ?>
          <a href="buku.php?hapus=<?= $b['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus buku ini?')">Hapus</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($bukuList)): ?>
      <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada data buku.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../src/layout_bottom.php'; ?>
