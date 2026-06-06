<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../src/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login – Perpustakaan</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:#fff;border-radius:16px;padding:2.5rem 2rem;width:100%;max-width:380px;box-shadow:0 4px 24px rgba(0,0,0,.08);}
.logo{text-align:center;margin-bottom:1.75rem;}
.logo-icon{width:56px;height:56px;background:#dbeafe;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;}
.logo-icon svg{width:28px;height:28px;color:#2563eb;}
h1{font-size:18px;font-weight:600;color:#0f172a;text-align:center;}
.sub{font-size:13px;color:#64748b;text-align:center;margin-top:4px;}
label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:5px;margin-top:1rem;}
input{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;transition:border .2s;}
input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.btn{width:100%;margin-top:1.5rem;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:500;cursor:pointer;transition:background .2s;}
.btn:hover{background:#1d4ed8;}
.error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;padding:10px 12px;font-size:13px;margin-top:1rem;}
.hint{margin-top:1.25rem;background:#f8fafc;border-radius:8px;padding:10px 12px;font-size:12px;color:#64748b;}
.hint b{color:#374151;}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
    </div>
    <h1>Perpustakaan Digital</h1>
    <p class="sub">Sistem Manajemen Perpustakaan</p>
  </div>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" placeholder="Masukkan username" required/>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Masukkan password" required/>
    <button type="submit" class="btn">Masuk</button>
  </form>

  <div class="hint">
    <b>Demo Login:</b><br>
    Admin: <b>admin</b> / <b>password</b><br>
    Petugas: <b>petugas</b> / <b>password</b>
  </div>
</div>
</body>
</html>
