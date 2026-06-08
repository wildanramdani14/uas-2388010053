# 📚 Dokumentasi UAS Administrasi Server
## Deployment Multi-Aplikasi dengan Docker & CI/CD Pipeline

**Nama:** Wildan Ramdani  
**NIM:** 2388010053  
**Mata Kuliah:** Administrasi Server (Cloud Computing II)  
**Dosen:** Mohamad Firdaus, M.Kom.

---

## 📋 Daftar Isi

1. [Arsitektur Sistem](#arsitektur-sistem)
2. [Persiapan](#persiapan)
3. [Step 1 - Membuat Web Statis](#step-1---membuat-web-statis)
4. [Step 2 - Membuat Web Dinamis PHP](#step-2---membuat-web-dinamis-php)
5. [Step 3 - Dockerize Aplikasi](#step-3---dockerize-aplikasi)
6. [Step 4 - Docker Compose](#step-4---docker-compose)
7. [Step 5 - Upload ke GitHub](#step-5---upload-ke-github)
8. [Step 6 - Setup AWS EC2](#step-6---setup-aws-ec2)
9. [Step 7 - GitHub Actions CI/CD](#step-7---github-actions-cicd)
10. [Step 8 - Deploy & Verifikasi](#step-8---deploy--verifikasi)
11. [Step 9 - Live Test Auto-Update](#step-9---live-test-auto-update)
12. [Hasil Akhir](#hasil-akhir)

---

## Arsitektur Sistem

```
Internet
    │
    ▼
[ Nginx Reverse Proxy :80 ]
    │                    │
    ▼                    ▼
[Web Statis]      [Web Dinamis PHP]
  /                /perpustakaan/
                         │
                         ▼
                   [MariaDB :3306]
```

| Service | Container | Port | Deskripsi |
|---|---|---|---|
| nginx-proxy | nginx-proxy | 80 | Reverse Proxy utama |
| web-static | web-static | 80 | Web CV Statis (Nginx) |
| web-dinamis | web-dinamis | 80 | Perpustakaan PHP |
| db | db | 3306 | MariaDB (internal) |

---

## Persiapan

### Tools yang Dibutuhkan
- Git ([git-scm.com](https://git-scm.com))
- VSCode ([code.visualstudio.com](https://code.visualstudio.com))
- Akun GitHub ([github.com](https://github.com))
- Akun Docker Hub ([hub.docker.com](https://hub.docker.com))
- Akun AWS ([aws.amazon.com](https://aws.amazon.com))

### Struktur Folder Project
```
uas-project/
├── web-static/
│   ├── index.html
│   └── Dockerfile
├── public/              ← Aplikasi PHP
│   ├── login.php
│   ├── index.php
│   ├── buku.php
│   ├── peminjaman.php
│   ├── users.php
│   └── logout.php
├── src/
│   ├── db.php
│   ├── auth.php
│   ├── layout_top.php
│   └── layout_bottom.php
├── docker/
│   └── init.sql
├── nginx/
│   └── default.conf
├── .github/
│   └── workflows/
│       └── deploy.yml
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Step 1 - Membuat Web Statis

Web statis berupa halaman CV pribadi menggunakan HTML dan CSS murni.

### File: `web-static/index.html`
Berisi informasi CV lengkap:
- Data Pribadi
- Pendidikan
- Organisasi
- Hobi
- Kemampuan

### File: `web-static/Dockerfile`
```dockerfile
FROM nginx:alpine
COPY index.html /usr/share/nginx/html/index.html
EXPOSE 80
```

### Screenshot Web Statis
<img width="960" height="600" alt="image" src="https://github.com/user-attachments/assets/25a290ff-f158-4ec9-b25a-abb54cc43e19" />


---

## Step 2 - Membuat Web Dinamis PHP

Aplikasi manajemen perpustakaan dengan fitur:
- Login (Admin & Petugas)
- CRUD Data Buku
- Pencatatan Peminjaman & Pengembalian
- Kelola Users (Admin only)

### Fitur Aplikasi

| Halaman | File | Deskripsi |
|---|---|---|
| Login | `login.php` | Autentikasi user |
| Dashboard | `index.php` | Statistik & data terbaru |
| Data Buku | `buku.php` | CRUD buku + pencarian |
| Peminjaman | `peminjaman.php` | Catat & kembalikan buku |
| Kelola Users | `users.php` | Manajemen akun (admin) |

### Akun Default

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Petugas | `petugas` | `password` |

### Screenshot Aplikasi

<img width="960" height="600" alt="image-1" src="https://github.com/user-attachments/assets/a5818809-d15d-4d75-81aa-c5d0e55107bc" />

<img width="960" height="600" alt="image-2" src="https://github.com/user-attachments/assets/bc52f336-967b-4247-8630-b6d0f371a298" />

<img width="960" height="600" alt="image-3" src="https://github.com/user-attachments/assets/b4527ac7-b717-4098-91e9-30e0e3d9e87c" />

<img width="960" height="600" alt="image-4" src="https://github.com/user-attachments/assets/4f35c642-d06c-461d-9696-2e9b057602a1" />


---

## Step 3 - Dockerize Aplikasi

### File: `Dockerfile` (Web Dinamis PHP)
```dockerfile
FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY public/ /var/www/html/
COPY src/ /var/www/src/

RUN chown -R www-data:www-data /var/www/html /var/www/src

EXPOSE 80
```

### File: `docker/init.sql`
File SQL untuk auto-seeding database MariaDB saat pertama kali dijalankan. Berisi:
- Pembuatan database & tabel (`users`, `buku`, `peminjaman`)
- Data awal: 2 user, 8 buku, 3 data peminjaman

---

## Step 4 - Docker Compose

### File: `docker-compose.yml`
```yaml
services:
  web-static:
    image: USERNAME/web-static-uas:latest
    container_name: web-static
    networks:
      - appnet

  web-dinamis:
    image: USERNAME/web-dinamis-uas:latest
    container_name: web-dinamis
    environment:
      DB_HOST: db
      DB_NAME: perpustakaan
      DB_USER: root
      DB_PASSWORD: secret
    depends_on:
      db:
        condition: service_healthy
    networks:
      - appnet

  db:
    image: mariadb:10.11
    container_name: db
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: perpustakaan
    volumes:
      - db_data:/var/lib/mysql
      - ./docker/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - appnet

  nginx:
    image: nginx:alpine
    container_name: nginx-proxy
    ports:
      - "80:80"
    volumes:
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - web-static
      - web-dinamis
    networks:
      - appnet

volumes:
  db_data:

networks:
  appnet:
    driver: bridge
```

### File: `nginx/default.conf`
```nginx
server {
    listen 80;

    location / {
        proxy_pass http://web-static:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    location /perpustakaan/ {
        proxy_pass http://web-dinamis:80/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_redirect off;
    }
}
```

---

## Step 5 - Upload ke GitHub

### Buat Repository Baru
1. Buka [github.com](https://github.com) → klik **New**
2. Nama repository: `UAS-NIM`
3. Visibility: **Public**
4. Klik **Create repository**

### Buat Personal Access Token (PAT)
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Klik **Generate new token (classic)**
3. Centang scope: **repo**
4. Klik **Generate token** → salin tokennya

### Push ke GitHub
```bash
git init
git add .
git commit -m "first commit: UAS MultiApp"
git branch -M main
git remote add origin https://github.com/USERNAME/UAS-NIM.git
git push -u origin main
```

<img width="960" height="600" alt="image-5" src="https://github.com/user-attachments/assets/c1280e68-e015-4bc8-9b7c-8c9ae589bc39" />

---

## Step 6 - Setup AWS EC2

### Buat Instance EC2
1. Buka **AWS Console** → **EC2** → **Launch Instance**
2. Konfigurasi:
   - Name: `UAS-NIM`
   - AMI: **Ubuntu Server 22.04 LTS**
   - Instance type: **t2.micro** (free tier)
   - Key pair: buat baru atau gunakan yang ada → download `.pem`
3. Security Group → tambahkan inbound rules:

| Type | Port | Source |
|---|---|---|
| SSH | 22 | 0.0.0.0/0 |
| HTTP | 80 | 0.0.0.0/0 |

4. Klik **Launch Instance**

### Pasang Elastic IP
1. EC2 → **Elastic IPs** → **Allocate Elastic IP**
2. Klik **Associate** → pilih instance → **Associate**

<img width="960" height="600" alt="image-6" src="https://github.com/user-attachments/assets/1a41e9e9-6714-4ab7-b11c-7b32f19b29d2" />


### Install Docker di EC2
Sambungkan ke EC2 via SSH:
```bash
ssh -i "nama-file.pem" ubuntu@IP_EC2
```

Jalankan perintah berikut:
```bash
sudo apt update && sudo apt upgrade -y

sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

sudo usermod -aG docker ubuntu
newgrp docker
```

Verifikasi:
```bash
docker --version
docker compose version
```

<img width="527" height="381" alt="image-7" src="https://github.com/user-attachments/assets/2fff6b58-d092-4bd1-ad52-90a7672c4c75" />


### Setup Auto-Start Container

Buat systemd service supaya container otomatis jalan saat instance dinyalakan:

```bash
sudo nano /etc/systemd/system/uas-app.service
```

Isi dengan:
```
[Unit]
Description=UAS App Docker Compose
After=docker.service
Requires=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/ubuntu/uas-app
ExecStart=/bin/bash -c 'docker rm -f web-static web-dinamis nginx-proxy db 2>/dev/null; docker compose up -d'
ExecStop=/usr/bin/docker compose down
TimeoutStartSec=300

[Install]
WantedBy=multi-user.target
```

Aktifkan:
```bash
sudo systemctl daemon-reload
sudo systemctl enable uas-app.service
```

---

## Step 7 - GitHub Actions CI/CD

### Setup GitHub Secrets
Buka repository → **Settings** → **Secrets and variables** → **Actions** → tambahkan:

| Secret | Nilai |
|---|---|
| `DOCKER_USERNAME` | Username Docker Hub |
| `DOCKER_PASSWORD` | Password Docker Hub |
| `EC2_HOST` | IP Public EC2 |
| `EC2_USER` | `ubuntu` |
| `EC2_SSH_KEY` | Isi file `.pem` (copy semua termasuk header) |

### File: `.github/workflows/deploy.yml`
```yaml
name: CI/CD Pipeline - UAS MultiApp

on:
  push:
    branches: [main]

jobs:
  build-static:
    name: Build Web Statis
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: docker/login-action@v3
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      - uses: docker/build-push-action@v5
        with:
          context: ./web-static
          push: true
          tags: ${{ secrets.DOCKER_USERNAME }}/web-static-uas:latest

  build-dinamis:
    name: Build Web Dinamis PHP
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: docker/login-action@v3
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      - uses: docker/build-push-action@v5
        with:
          context: .
          push: true
          tags: ${{ secrets.DOCKER_USERNAME }}/web-dinamis-uas:latest

  deploy:
    name: Deploy ke AWS EC2
    runs-on: ubuntu-latest
    needs: [build-static, build-dinamis]
    steps:
      - uses: actions/checkout@v4
      - uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.EC2_HOST }}
          username: ${{ secrets.EC2_USER }}
          key: ${{ secrets.EC2_SSH_KEY }}
          source: "docker-compose.yml,nginx/,docker/"
          target: "~/uas-app"
      - uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.EC2_HOST }}
          username: ${{ secrets.EC2_USER }}
          key: ${{ secrets.EC2_SSH_KEY }}
          script: |
            cd ~/uas-app
            docker pull ${{ secrets.DOCKER_USERNAME }}/web-static-uas:latest
            docker pull ${{ secrets.DOCKER_USERNAME }}/web-dinamis-uas:latest
            docker stop web-static || true
            docker rm web-static || true
            docker run -d --name web-static --network uas-app_appnet ${{ secrets.DOCKER_USERNAME }}/web-static-uas:latest
            docker compose up -d --no-deps --force-recreate web-dinamis nginx
            docker image prune -f
            echo "Deploy selesai $(date)"
```

<img width="960" height="600" alt="image-8" src="https://github.com/user-attachments/assets/611fcdc6-abdb-4f01-9c9b-8b61daeb45ec" />


---

## Step 8 - Deploy & Verifikasi

### Jalankan Pertama Kali di EC2
```bash
cd ~/uas-app
docker compose up -d
```

### Verifikasi Container Berjalan
```bash
docker ps
```

Output yang diharapkan:
```
CONTAINER ID   IMAGE                          PORTS                NAMES
xxx            nginx:alpine                   0.0.0.0:80->80/tcp   nginx-proxy
xxx            USERNAME/web-dinamis-uas       80/tcp               web-dinamis
xxx            mariadb:10.11                  3306/tcp             db
xxx            USERNAME/web-static-uas        80/tcp               web-static
```

<img width="863" height="500" alt="image-9" src="https://github.com/user-attachments/assets/8c925eb8-5955-4708-96a8-e987693eaa81" />


### Akses Aplikasi di Browser

| Aplikasi | URL |
|---|---|
| Web CV Statis | `http://IP_EC2/` |
| Perpustakaan (Login) | `http://IP_EC2/perpustakaan/login.php` |

<img width="960" height="600" alt="image-10" src="https://github.com/user-attachments/assets/9c7e8310-c1d5-4241-b4ac-40968f9d3af8" />

<img width="960" height="600" alt="image-11" src="https://github.com/user-attachments/assets/cd4c9457-faee-4e63-9917-4291c609bf16" />

<img width="960" height="600" alt="image-12" src="https://github.com/user-attachments/assets/acb7bdcf-bb40-44b8-ae2a-e10e7571a1b4" />


---

## Step 9 - Live Test Auto-Update

Ini adalah bukti bahwa CI/CD pipeline bekerja otomatis — setiap perubahan kode yang di-push ke GitHub akan langsung ter-deploy ke server AWS tanpa proses manual.

### Cara Test

**1. Edit kode di VSCode**

Buka file `web-static/index.html`, ubah sesuatu (contoh: ganti teks footer).

Sebelum:
```html
© 2025 Wildan Ramdani — Web CV
```

Sesudah:
```html
© 2026 Wildan Ramdani — Web CV | UAS Administrasi Server
```

**2. Commit & Push lewat VSCode**

- Buka tab **Source Control** di VSCode (ikon cabang di sidebar kiri)
- Ketik pesan commit: `live test: update footer`
- Klik **Commit** lalu **Sync Changes**

**3. Pantau Pipeline di GitHub Actions**

Buka: `github.com/USERNAME/REPO/actions`

Pipeline akan berjalan otomatis dengan 3 job:
1. ✅ Build Web Statis
2. ✅ Build Web Dinamis PHP
3. ✅ Deploy ke AWS EC2

**4. Verifikasi di Browser**

Refresh halaman `http://IP_EC2/` — perubahan langsung muncul tanpa perlu manual apapun ke server!

## Hasil Akhir

### Ringkasan Komponen yang Berhasil Dibuat

| Komponen | Status | Keterangan |
|---|---|---|
| Web Statis | ✅ | Web CV HTML/CSS, served via Nginx |
| Web Dinamis | ✅ | Manajemen Perpustakaan PHP + MariaDB |
| Docker Compose | ✅ | 4 service: nginx, web-static, web-dinamis, db |
| Auto-seeding DB | ✅ | `init.sql` berjalan otomatis saat container pertama dibuat |
| GitHub Actions | ✅ | 3 job: build-static, build-dinamis, deploy |
| Deploy AWS EC2 | ✅ | Instance `UAS-2388010053` dengan Elastic IP |
| Auto-start | ✅ | Systemd service — container otomatis jalan saat instance start |
| Live Test | ✅ | Push kode → pipeline → server terupdate otomatis |

### Link Akses

| | URL |
|---|---|
| Repository GitHub | https://github.com/wildanramdani14/uas-2388010053 |
| Web Statis | http://IP_EC2/ |
| Web Dinamis | http://IP_EC2/perpustakaan/login.php |
| Docker Hub | https://hub.docker.com/u/kakangwildan |

---

*Dokumentasi ini dibuat sebagai bagian dari UAS Mata Kuliah Administrasi Server*  
*Wildan Ramdani — 2388010053*
