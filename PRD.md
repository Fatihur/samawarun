

# PRD – Website Komunitas Lari Samawa Run

## 1. Product Overview

**Nama Produk:** Samawa Run
**Platform:** Website

### Teknologi

* **Backend:** Laravel 11
* **Frontend:** Livewire + Blade
* **Database:** MySQL

### Tujuan Produk

Website ini bertujuan untuk:

* Menjadi **website resmi komunitas lari Samawa Run di Sumbawa**
* Menyediakan **informasi komunitas dan event lari**
* Memudahkan peserta untuk **mendaftar event**
* Memudahkan admin untuk **mengelola event dan peserta**

### Target Pengguna

1. **Pengunjung Website**
   Mengakses informasi komunitas dan event.

2. **Peserta Event**
   Mendaftar dan mengikuti event lari.

3. **Admin Komunitas**
   Mengelola event, peserta, dan informasi website.

---

# 2. Scope MVP

Versi **MVP (Minimum Viable Product)** terdiri dari dua modul utama:

1. **Landing Page (Public Website)**
2. **Dashboard Admin**

Tujuan MVP adalah agar sistem dapat **segera digunakan untuk pendaftaran event pertama**.

---

# 3. Landing Page (Public Website)

Landing page berfungsi sebagai **website utama komunitas Samawa Run**.

## 3.1 Halaman Home

Halaman utama yang menampilkan informasi komunitas.

### Section

#### Hero Section

Menampilkan identitas utama komunitas.

Konten:

* Nama komunitas
* Tagline
* Tombol:

  * **Lihat Event**
  * **Daftar Event**

---

#### About Samawa Run

Informasi tentang komunitas.

Konten:

* Deskripsi komunitas
* Visi komunitas
* Tujuan komunitas

---

#### Upcoming Event

Menampilkan daftar **event yang sedang aktif**.

Informasi yang ditampilkan:

* Poster event
* Nama event
* Tanggal event
* Lokasi
* Jarak lomba (5K / 7K / 10K)
* Tombol **Detail Event**

---

#### Gallery / Aktivitas Komunitas (Optional)

Menampilkan dokumentasi kegiatan komunitas.

---

#### Contact

Menampilkan informasi kontak komunitas.

Informasi:

* Nomor HP
* WhatsApp
* Email
* Sosial media

---

# 3.2 Halaman Event

Halaman yang menampilkan **daftar seluruh event**.

Informasi yang ditampilkan:

* Poster event
* Nama event
* Tanggal
* Lokasi
* Kategori
* Jarak lomba
* Biaya pendaftaran
* Tombol **Detail**

---

# 3.3 Halaman Detail Event

Menampilkan informasi lengkap event.

Informasi yang ditampilkan:

* Poster event
* Nama event
* Deskripsi
* Tanggal event
* Lokasi
* Kategori
* Jarak lomba
* Biaya pendaftaran
* Kontak panitia
* Nomor rekening pembayaran

Tombol utama:

**Daftar Event**

---

# 3.4 Form Pendaftaran Event

Form yang digunakan peserta untuk mendaftar.

### Data Peserta

* Nama lengkap
* Tanggal lahir
* Jenis kelamin
* NIK
* Upload KTP
* Alamat

### Informasi Kontak

* Nomor HP
* Email
* Kontak darurat

### Detail Event

* Pilih event
* Kategori jarak:

  * 5K
  * 7K
  * 10K

### Merchandise

Ukuran jersey:

* S
* M
* L
* XL
* XXL

### Pembayaran

* Upload bukti transfer

### Status Pendaftaran

Setelah form dikirim:

* Data peserta masuk ke sistem
* Status peserta = **Pending Verifikasi**

---

# 4. Dashboard Admin

Dashboard admin digunakan untuk **mengelola seluruh data sistem**.

### URL Akses

```
/admin
```

### Autentikasi

Login menggunakan:

* Email
* Password

---

# 5. Fitur Dashboard Admin

## 5.1 Kelola Kontak

Digunakan untuk mengelola informasi kontak yang ditampilkan di landing page.

### Field

* Nomor HP
* WhatsApp
* Email
* Instagram
* Facebook
* TikTok
* Alamat

### Fitur

* Edit kontak
* Update informasi

---

## 5.2 Kelola Event

Admin dapat membuat dan mengelola event.

### Field Event

* ID
* Nama event
* Poster
* Deskripsi
* Tanggal event
* Lokasi / tempat
* Biaya pendaftaran
* Kontak panitia
* Nomor rekening
* Status aktif (aktif / nonaktif)

### Fitur

* Tambah event
* Edit event
* Hapus event
* Aktif / nonaktif event

---

## 5.3 Kelola Peserta

Menampilkan daftar peserta yang mendaftar event.

### Data Peserta

* ID
* ID Event
* **Bib Number (auto generate)**
* Nama
* Tanggal lahir
* Jenis kelamin
* NIK
* Nomor HP
* Email
* Alamat
* Kategori jarak
* Ukuran jersey
* Kontak darurat
* Bukti transfer
* Status verifikasi

### Status Peserta

* **Pending**
* **Verified**
* **Rejected**

### Fitur

* Lihat detail peserta
* Verifikasi peserta
* Tolak peserta
* Export data peserta

---

# 6. Sistem Nomor Dada (BIB)

## Konsep

Nomor BIB dibuat berdasarkan:

**Kode jarak + nomor urut pendaftaran**

Format:

```
[JARAK][URUT]
```

---

## Contoh Format

| Jarak | Nomor Urut | BIB   |
| ----- | ---------- | ----- |
| 5K    | 001        | 5001  |
| 5K    | 002        | 5002  |
| 7K    | 001        | 7001  |
| 7K    | 002        | 7002  |
| 10K   | 001        | 10001 |

---

## Prefix Berdasarkan Jarak

### Kategori 5K

Prefix: `5`

Contoh:

```
5001
5002
5003
```

---

### Kategori 7K

Prefix: `7`

Contoh:

```
7001
7002
7003
```

---

### Kategori 10K

Prefix: `10`

Contoh:

```
10001
10002
10003
```

---

# 7. Flow Generate BIB

Bib number dibuat setelah peserta diverifikasi oleh admin.

Flow:

```
Peserta mendaftar
        ↓
Upload bukti transfer
        ↓
Status = Pending
        ↓
Admin verifikasi
        ↓
Generate Bib Number
```

---

# 8. Algoritma Generate BIB

Langkah proses:

1. Ambil kategori jarak peserta
2. Hitung jumlah peserta sebelumnya pada kategori tersebut
3. Tambahkan +1
4. Gabungkan prefix jarak dengan nomor urut

Contoh pseudo code Laravel:

```php
$distance = $participant->distance;

$count = Participant::where('event_id', $eventId)
    ->where('distance', $distance)
    ->whereNotNull('bib_number')
    ->count();

$number = $count + 1;

$bib = $distance . str_pad($number, 3, '0', STR_PAD_LEFT);
```

Contoh hasil:

```
5001
7001
10001
```

---

# 9. Generate ID Card Peserta

Admin dapat menghasilkan **ID Card / Race Card** untuk peserta.

### Informasi pada ID Card

* Nama peserta
* Bib number
* Nama event
* Kategori jarak
* QR Code (optional)

### Fitur

* Generate PDF
* Download
* Print

---

# 10. Database Design (MySQL)

## Table: contacts

```
id
phone
whatsapp
email
instagram
facebook
tiktok
address
created_at
updated_at
```

---

## Table: events

```
id
event_code
name
poster
description
date
location
price
contact
bank_account
is_active
created_at
updated_at
```

---

## Table: participants

```
id
event_id
bib_number
name
birth_date
gender
nik
ktp_file
phone
email
address
distance_category
jersey_size
emergency_contact
transfer_proof
status
created_at
updated_at
```

---

# 11. Non Functional Requirements

### Performance

* Website cepat
* Optimasi untuk mobile

### Security

* Validasi input form
* Upload file aman
* Proteksi login admin

### Usability

* UI sederhana
* Mudah digunakan

---

# 12. Future Features (Post MVP)

Fitur lanjutan yang dapat ditambahkan:

* QR Code check-in peserta
* Sistem timing race
* Leaderboard hasil lomba
* Sertifikat otomatis
* Payment gateway
* Dashboard peserta
* Email notifikasi

---

# 13. Struktur Folder Laravel (Rekomendasi)

```
app
 ├── Models
 │    ├── Event.php
 │    ├── Participant.php
 │    └── Contact.php
 │
 ├── Livewire
 │    ├── Event
 │    ├── Participant
 │    └── Registration
```

---

# 14. Flow Pendaftaran Event

Alur pengguna saat mendaftar event:

```
Landing Page
     ↓
Klik Event
     ↓
Detail Event
     ↓
Daftar Event
     ↓
Isi Form Pendaftaran
     ↓
Upload Bukti Transfer
     ↓
Submit
     ↓
Admin Verifikasi
     ↓
Generate Bib Number
```
