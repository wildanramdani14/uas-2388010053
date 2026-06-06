# 📚 UAS Administrasi Server – MultiApp Deployment

**Nama:** Wildan Ramdani  
**NIM:** _(isi NIM kamu)_  
**Mata Kuliah:** Administrasi Server (Cloud Computing II)  
**Dosen:** Mohamad Firdaus, M.Kom.

---

## 🏗️ Arsitektur Sistem

```
Internet
    │
    ▼
[ Nginx Reverse Proxy :80 ]
    │              │
    ▼              ▼
[Web Statis]   [Web Dinamis PHP]
  /              /perpustakaan/
                      │
                      ▼
                 [MariaDB :3306]
```

| Service | Container | Port Internal | Deskripsi |
|---|---|---|---|
| nginx-proxy | nginx-proxy | 80 | Reverse Proxy utama |
| web-static | web-static | 80 | Web CV Statis (Nginx) |
| web-dinamis | web-dinamis | 80 | Perpustakaan PHP |
| db | db | 3306 | MariaDB (internal only) |

---

## 🚀 Cara Menjalankan

### Prasyarat
- Docker & Docker Compose
- Git

### Clone & Run
```bash
git clone https://github.com/<username>/<repo>.git
cd <repo>
docker compose up -d
```

### Akses Aplikasi
| Aplikasi | URL |
|---|---|
| Web CV Statis | `http://<IP-AWS>/` |
| Perpustakaan (Login) | `http://<IP-AWS>/perpustakaan/login.php` |

### Login Default
| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Petugas | `petugas` | `password` |

---

## ⚙️ CI/CD Pipeline (GitHub Actions)

Pipeline otomatis berjalan setiap `git push` ke branch `main`.

### Alur Pipeline
```
git push → GitHub Actions Trigger
    │
    ├─ Job 1: Build & Push web-static → Docker Hub
    ├─ Job 2: Build & Push web-dinamis → Docker Hub
    │
    └─ Job 3: Deploy ke AWS EC2
           ├─ SCP docker-compose.yml ke EC2
           ├─ SSH: docker pull (image terbaru)
           └─ SSH: docker compose up -d (Zero Downtime)
```

### GitHub Secrets yang Diperlukan
| Secret | Keterangan |
|---|---|
| `DOCKER_USERNAME` | Username Docker Hub |
| `DOCKER_PASSWORD` | Password / Token Docker Hub |
| `EC2_HOST` | IP Public AWS EC2 |
| `EC2_USER` | User SSH (biasanya `ubuntu`) |
| `EC2_SSH_KEY` | Private key `.pem` EC2 |

---

## 🐳 Docker Compose

- **web-static**: Nginx Alpine serve HTML statis
- **web-dinamis**: PHP 8.2 + Apache, terhubung ke MariaDB via DNS internal (`db`)
- **db**: MariaDB 10.11, auto-seeding dari `init.sql` saat pertama kali dijalankan
- **nginx**: Reverse proxy, routing `/` → web-static, `/perpustakaan/` → web-dinamis

### Environment Variables
```env
DB_HOST=db           # DNS internal Docker network
DB_NAME=perpustakaan
DB_USER=root
DB_PASSWORD=secret
```

---

## 🗄️ Database

Database MariaDB ter-seed otomatis dari `/docker-entrypoint-initdb.d/init.sql` yang berisi:
- Tabel `users` (admin + petugas)
- Tabel `buku` (8 data awal)
- Tabel `peminjaman` (3 contoh data)

---

## 📸 Screenshot

_(Tambahkan screenshot berikut setelah deploy)_

- [ ] GitHub Actions – pipeline sukses (centang hijau)
- [ ] Web Statis berjalan di IP AWS
- [ ] Halaman Login perpustakaan
- [ ] Dashboard perpustakaan
- [ ] `docker compose ps` – semua container Up
- [ ] Live Test: perubahan kode → auto-update di AWS

---

## 🔗 Link

- **Repository:** https://github.com/<username>/<repo>
- **Web Statis:** http://<IP-AWS>/
- **Web Dinamis:** http://<IP-AWS>/perpustakaan/login.php
- **Docker Hub:** https://hub.docker.com/u/<username>
