<?php
// layout.php — include di setiap halaman
// Variable $pageTitle harus di-set sebelum include
$pageTitle = $pageTitle ?? 'Dashboard';
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle) ?> – Perpustakaan</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a1a2e;min-height:100vh;display:flex;}

/* Sidebar */
.sidebar{width:220px;background:#fff;border-right:1px solid #e2e8f0;display:flex;flex-direction:column;min-height:100vh;position:fixed;top:0;left:0;}
.sidebar-logo{padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;}
.sidebar-logo svg{width:22px;height:22px;color:#2563eb;flex-shrink:0;}
.sidebar-logo span{font-size:14px;font-weight:600;color:#0f172a;}
nav{padding:1rem 0;flex:1;}
nav a{display:flex;align-items:center;gap:10px;padding:9px 1.5rem;font-size:13.5px;color:#475569;text-decoration:none;transition:all .15s;}
nav a:hover{background:#f8fafc;color:#1d4ed8;}
nav a.active{background:#eff6ff;color:#2563eb;font-weight:500;}
nav a svg{width:17px;height:17px;flex-shrink:0;}
.sidebar-user{padding:1rem 1.5rem;border-top:1px solid #f1f5f9;font-size:12px;}
.sidebar-user .uname{font-weight:500;color:#0f172a;}
.sidebar-user .urole{color:#94a3b8;margin-top:1px;text-transform:capitalize;}
.sidebar-user a{display:inline-block;margin-top:8px;font-size:12px;color:#ef4444;text-decoration:none;}
.sidebar-user a:hover{text-decoration:underline;}

/* Main */
.main{margin-left:220px;flex:1;padding:2rem;}
.page-header{margin-bottom:1.5rem;}
.page-header h1{font-size:20px;font-weight:600;color:#0f172a;}
.page-header p{font-size:13px;color:#64748b;margin-top:3px;}

/* Cards */
.card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem 1.5rem;}

/* Stat grid */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;}
.stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1rem 1.25rem;}
.stat-label{font-size:12px;color:#64748b;margin-bottom:6px;}
.stat-val{font-size:24px;font-weight:600;color:#0f172a;}
.stat-icon{float:right;margin-top:-2px;}
.stat-icon svg{width:20px;height:20px;}

/* Table */
table{width:100%;border-collapse:collapse;font-size:13.5px;}
th{text-align:left;padding:10px 12px;font-size:12px;font-weight:500;color:#64748b;border-bottom:1px solid #e2e8f0;background:#f8fafc;}
td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafbff;}

/* Badges */
.badge{display:inline-block;font-size:11px;font-weight:500;padding:3px 9px;border-radius:999px;}
.badge-blue{background:#dbeafe;color:#1d4ed8;}
.badge-green{background:#dcfce7;color:#15803d;}
.badge-red{background:#fee2e2;color:#dc2626;}
.badge-amber{background:#fef3c7;color:#b45309;}
.badge-gray{background:#f1f5f9;color:#475569;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:all .15s;}
.btn-primary{background:#2563eb;color:#fff;}
.btn-primary:hover{background:#1d4ed8;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
.btn-danger:hover{background:#fee2e2;}
.btn-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.btn-success:hover{background:#dcfce7;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn svg{width:14px;height:14px;}

/* Forms */
.form-group{margin-bottom:1rem;}
.form-group label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:5px;}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13.5px;font-family:inherit;outline:none;transition:border .2s;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}

/* Alert */
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem;}
.alert-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
    <span>Perpustakaan</span>
  </div>
  <nav>
    <a href="index.php" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
      Dashboard
    </a>
    <a href="buku.php" <?= basename($_SERVER['PHP_SELF']) === 'buku.php' ? 'class="active"' : '' ?>>
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
      Data Buku
    </a>
    <a href="peminjaman.php" <?= basename($_SERVER['PHP_SELF']) === 'peminjaman.php' ? 'class="active"' : '' ?>>
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
      Peminjaman
    </a>
    <?php if (isAdmin()): ?>
    <a href="users.php" <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'class="active"' : '' ?>>
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
      Kelola Users
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-user">
    <div class="uname"><?= htmlspecialchars($user['nama']) ?></div>
    <div class="urole"><?= $user['role'] ?></div>
    <a href="logout.php">Keluar →</a>
  </div>
</aside>

<main class="main">
