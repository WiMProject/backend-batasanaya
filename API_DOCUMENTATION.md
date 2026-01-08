# 📘 Batasanaya API Documentation

Dokumentasi lengkap untuk endpoint REST API Batasanaya Backend.

**Base URL:** `http://localhost:8000/api`

---

## 📑 Daftar Isi

1. [Authentication](#-authentication)
2. [User Management](#-user-management)
3. [Asset Management (CMS)](#-asset-management)
4. [Video & Streaming](#-video--streaming)
5. [Game Engine: Cari Hijaiyyah](#-game-cari-hijaiyyah)
6. [Game Engine: Pasangkan Huruf](#-game-pasangkan-huruf)
7. [Admin Routes](#-admin-routes)

---

## 🔐 Authentication

Auth moduke menangani registrasi, login, dan keamanan berbasis token (JWT).

### 1. Register User
Mendaftarkan pengguna baru ke dalam sistem.

- **URL:** `/auth/register`
- **Method:** `POST`
- **Auth Required:** No

**Body:**
```json
{
    "fullName": "John Doe",
    "email": "john.doe@example.com",
    "phone_number": "081234567890",
    "password": "password123"
}
```

**Response (201 Created):**
```json
{
    "message": "User successfully registered",
    "user": {
        "id": "uuid-string",
        "full_name": "John Doe",
        "email": "john.doe@example.com",
        "role": "user"
    }
}
```

### 2. Login User
Masuk sebagai user untuk mendapatkan Access Token.

- **URL:** `/auth/login`
- **Method:** `POST`
- **Auth Required:** No

**Body:**
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Response (200 OK):**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

### 3. Login Admin
Login khusus untuk dashboard admin web.

- **URL:** `/auth/admin-login`
- **Method:** `POST`
- **Auth Required:** No

**Body:**
```json
{
    "email": "admin@example.com",
    "password": "secretpassword"
}
```

---

## 👤 User Management

Modul untuk mengelola profil pengguna. **Membutuhkan Header Authorization.**

### 1. Get My Profile
Mengambil data detail profil user yang sedang login.

- **URL:** `/auth/me`
- **Method:** `GET`
- **Header:** `Authorization: Bearer <token>`

**Response (200 OK):**
```json
{
    "id": "uuid-string",
    "full_name": "John Doe",
    "email": "john.doe@example.com",
    "phone_number": "081234567890",
    "profile_picture": "uploads/profiles/pic123.jpg",
    "role": {
        "id": 1,
        "name": "user"
    }
}
```

### 2. Update Profile
Memperbarui informasi dasar user.

- **URL:** `/users/{id}`
- **Method:** `PATCH`
- **Header:** `Authorization: Bearer <token>`

**Body (Optional Fields):**
```json
{
    "fullName": "John Updated",
    "email": "new.email@example.com"
}
```

### 3. Upload Profile Picture
Mengganti foto profil user.

- **URL:** `/user/profile-picture`
- **Method:** `POST`
- **Content-Type:** `multipart/form-data`
- **Header:** `Authorization: Bearer <token>`

**Form Data:**
- `profile_picture`: File (jpg, jpeg, png) - Max 2MB

---

## 📦 Asset Management

Modul manajemen konten untuk sinkronisasi aset game.

### 1. Get Asset Manifest
Endpoint KRUSIAL untuk sinkronisasi data. Frontend harus memanggil ini untuk mengecek apakah ada aset baru yang perlu didownload.

- **URL:** `/assets/manifest`
- **Method:** `GET`
- **Query Params:** `type` (optional: `image` or `audio`)

**Response (200 OK):**
```json
{
    "version": 1704680000,
    "total_assets": 150,
    "assets": [
        {
            "id": "asset-uuid-1",
            "filename": "background_main.png",
            "type": "image",
            "size": 102400,
            "checksum": "d41d8cd98f00b204e9800998ecf8427e",
            "download_url": "http://api/assets/asset-uuid-1/file",
            "last_modified": 1704680000
        }
    ]
}
```

### 2. Download Asset
Mengunduh file fisik aset.

- **URL:** `/assets/{id}/file`
- **Method:** `GET`
- **Auth Required:** No

---

## 🎥 Video & Streaming

Modul untuk streaming konten video pembelajaran.

### 1. Get Video Details
Mendapatkan metadata video dan URL streaming HLS.

- **URL:** `/videos/{id}`
- **Method:** `GET`
- **Header:** `Authorization: Bearer <token>`

**Response (200 OK):**
```json
{
    "id": "video-uuid",
    "title": "Belajar Huruf Alif",
    "thumbnail": "uploads/videos/thumb_123.jpg",
    "stream_url": "http://api/videos/video-uuid/file" 
}
```
*Note: `stream_url` akan mengembalikan file `.m3u8` master playlist.*

---

## 🕌 Game Cari Hijaiyyah

API untuk logika permainan "Cari Huruf Hijaiyyah".

### 1. Get User Progress
Melihat level mana saja yang sudah terbuka dan selesai.

- **URL:** `/games/carihijaiyah/progress`
- **Method:** `GET`
- **Header:** `Authorization: Bearer <token>`

**Response (200 OK):**
```json
{
    "levels": [
        {
            "level_number": 1,
            "is_unlocked": true,
            "is_completed": true,
            "stars": 3
        },
        {
            "level_number": 2,
            "is_unlocked": true,
            "is_completed": false,
            "stars": 0
        }
    ]
}
```

### 2. Start Level
Memulai sesi permainan baru. Response berisi `session_id` yang **wajib** disimpan untuk request finish.

- **URL:** `/games/carihijaiyah/start`
- **Method:** `POST`
- **Header:** `Authorization: Bearer <token>`

**Body:**
```json
{
    "level_number": 2
}
```

**Response (200 OK):**
```json
{
    "message": "Game started",
    "session_id": "session-uuid-123",
    "server_timestamp": 1704681234
}
```

### 3. Finish Level
Menyelesaikan level dan menyimpan skor. Server akan memvalidasi durasi & logic jika diperlukan.

- **URL:** `/games/carihijaiyah/finish`
- **Method:** `POST`
- **Header:** `Authorization: Bearer <token>`

**Body:**
```json
{
    "session_id": "session-uuid-123",
    "level_number": 2,
    "score": 100,
    "attempts": 1
}
```

**Response (200 OK):**
```json
{
    "message": "Level completed!",
    "unlocked_next_level": true
}
```

---

## 🧩 Game Pasangkan Huruf

API untuk logika permainan "Pasangkan Huruf".
*Struktur endpoint sama persis dengan Cari Hijaiyyah, hanya berbeda path.*

- **Get Progress:** `GET /games/pasangkanhuruf/progress`
- **Start Level:** `POST /games/pasangkanhuruf/start`
- **Finish Level:** `POST /games/pasangkanhuruf/finish`

---

## 🛡️ Admin Routes

Endpoint khusus Dashboard Admin. **Memerlukan Token Admin.**

- **Dashboard Stats:** `GET /admin/stats`
- **List All Users:** `GET /admin/users`
- **User Game Details:** `GET /admin/users/{userId}/game-progress`
- **Create Admin/User:** `POST /users`
- **Delete User:** `DELETE /users/{id}`
- **List Assets:** `GET /assets`
- **Upload Asset:** `POST /assets`
- **Delete Asset:** `DELETE /assets/{id}`
