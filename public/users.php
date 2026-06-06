<?php
require_once __DIR__ . '/../src/auth.php';
$user = requireLogin();
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../src/db.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
        $stmt->execute([trim($_POST['nama']), trim($_POST['username']), $hash, $_POST['role']]);
        $msg = 'User berhasil ditambahkan.';
        $msgType = 'success';
    } catch (PDOException $e) {
        $msg = 'Username sudah digunakan.';
        $msgType = 'error';
    }
}

if (isset($_GET['hapus']) && intval($_GET['hapus']) !== $user['id']) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([intval($_GET['hapus'])]);
    $msg = 'User dihapus.';
    $msgType = 'success';
}

$users = $pdo->query("SELECT id, nama, username, role, created_at FROM users ORDER BY id")->fetchAll();

$pageTitle = 'Kelola Users';
require_once __DIR__ . '/../src/layout_top.php';
?>

<div class="page-header">
  <h1>Kelola Users</h1>
  <p>Manajemen akun pengguna sistem</p>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1.5rem;">
  <h2 style="font-size:15px;font-weight:600;margin-bottom:1rem;color:#0f172a;">Tambah User Baru</h2>
  <form method="POST">
    <input type="hidden" name="action" value="tambah"/>
    <div class="form-row">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required placeholder="Nama lengkap"/>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required placeholder="Username unik"/>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Minimal 6 karakter" minlength="6"/>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="petugas">Petugas</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Tambah User</button>
  </form>
</div>

<div class="card">
  <h2 style="font-size:15px;font-weight:600;margin-bottom:1rem;color:#0f172a;">Daftar User (<?= count($users) ?>)</h2>
  <table>
    <thead>
      <tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $i => $u): ?>
      <tr>
        <td style="color:#94a3b8;"><?= $i+1 ?></td>
        <td style="font-weight:500;"><?= htmlspecialchars($u['nama']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td>
          <?php if ($u['role'] === 'admin'): ?>
            <span class="badge badge-blue">Admin</span>
          <?php else: ?>
            <span class="badge badge-gray">Petugas</span>
          <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if ($u['id'] !== $user['id']): ?>
          <a href="?hapus=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">Hapus</a>
          <?php else: ?>
          <span style="font-size:12px;color:#94a3b8;">Akun kamu</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../src/layout_bottom.php'; ?>
