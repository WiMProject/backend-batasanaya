# Admin Game Progress Features

## Overview
Admin dapat melihat progress game "Cari Hijaiyyah" dari semua user melalui dashboard admin.

## Features

### 1. User Management dengan Game Progress
Di tabel **Users**, admin bisa lihat:
- **Completed Levels**: Berapa level yang sudah diselesaikan (contoh: 5/17)
- **Total Stars**: Total bintang yang dikumpulkan (⭐)
- **Game Button**: Tombol gamepad untuk lihat detail lengkap

### 2. Detail Game Progress Modal
Klik tombol gamepad (🎮) untuk melihat:

#### Stats Summary
- **Completed**: Jumlah level yang diselesaikan dari 17 level
- **Stars**: Total bintang yang dikumpulkan
- **Total Score**: Akumulasi score dari semua level
- **Avg Accuracy**: Rata-rata akurasi dari semua session

#### Level Progress Table (17 Levels)
Untuk setiap level menampilkan:
- **Level Number**: Level 1-17
- **Status**: Unlocked/Locked
- **Completion**: Completed/Not Completed
- **Best Score**: Score tertinggi di level tersebut
- **Best Time**: Waktu tercepat (dalam detik)
- **Stars**: Bintang yang didapat (0-3 ⭐)
- **Attempts**: Berapa kali user mencoba level ini

#### Recent Sessions (Last 10)
History 10 gameplay terakhir:
- **Level**: Level yang dimainkan
- **Score**: Score yang didapat
- **Time**: Waktu yang dibutuhkan
- **Matches**: Correct/Total matches
- **Accuracy**: Persentase akurasi
- **Stars**: Bintang yang didapat
- **Completed At**: Tanggal dan waktu selesai

## API Endpoints

### Get All Users with Game Progress
```http
GET /api/admin/users
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "data": [
    {
      "id": "uuid",
      "full_name": "John Doe",
      "email": "john@example.com",
      "game_progress": {
        "completed_levels": 5,
        "total_levels": 17,
        "total_stars": 12,
        "total_score": 4500
      }
    }
  ]
}
```

### Get User Game Progress Detail
```http
GET /api/admin/users/{userId}/game-progress
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "user": {
    "id": "uuid",
    "full_name": "John Doe",
    "email": "john@example.com"
  },
  "progress": [
    {
      "level_number": 1,
      "is_unlocked": true,
      "is_completed": true,
      "best_score": 850,
      "best_time": 45,
      "stars": 3,
      "attempts": 2
    }
  ],
  "recent_sessions": [
    {
      "level_number": 1,
      "score": 850,
      "time_taken": 45,
      "correct_matches": 6,
      "wrong_matches": 0,
      "stars": 3,
      "completed_at": "2025-01-22 10:30:00"
    }
  ],
  "stats": {
    "total_completed": 5,
    "total_stars": 12,
    "total_score": 4500,
    "total_attempts": 15,
    "total_sessions": 20,
    "avg_accuracy": 85.5
  }
}
```

## How to Use

1. **Login ke Admin Dashboard**
   - URL: `http://lumen-backend-batasanaya.test/admin/login`
   - Email: `admin@test.com`
   - Password: `admin123`

2. **Buka Tab Users**
   - Klik menu "Users" di sidebar

3. **Lihat Game Progress**
   - Kolom "Game Progress" menampilkan ringkasan (5/17 ⭐ 12)
   - Klik tombol gamepad (🎮) untuk detail lengkap

4. **Analisis Progress**
   - Lihat level mana yang paling sulit (banyak attempts, sedikit completion)
   - Monitor user engagement (berapa user yang main sampai level berapa)
   - Identifikasi user yang stuck di level tertentu

## Use Cases

### 1. Monitoring User Engagement
Admin bisa lihat:
- Berapa persen user yang menyelesaikan level 1?
- Di level berapa user biasanya berhenti main?
- User mana yang paling aktif?

### 2. Game Balancing
Admin bisa analisis:
- Level mana yang terlalu sulit? (banyak attempts, rendah completion rate)
- Level mana yang terlalu mudah? (semua user dapat 3 bintang)
- Perlu adjust difficulty level tertentu?

### 3. User Support
Admin bisa:
- Lihat user yang stuck di level tertentu
- Identifikasi masalah gameplay
- Provide targeted help

## Database Tables

### carihijaiyah_progress
Menyimpan progress per level per user:
- `user_id`: UUID user
- `level_number`: 1-17
- `is_unlocked`: Boolean
- `is_completed`: Boolean
- `best_score`: Integer
- `best_time`: Integer (seconds)
- `stars`: 0-3
- `attempts`: Integer

### carihijaiyah_sessions
Menyimpan history setiap gameplay:
- `user_id`: UUID user
- `level_number`: 1-17
- `score`: Integer
- `time_taken`: Integer (seconds)
- `correct_matches`: Integer
- `wrong_matches`: Integer
- `stars`: 0-3
- `completed_at`: Timestamp

## Notes

- Progress otomatis dibuat saat user pertama kali akses game
- Level 1 otomatis unlocked
- Level selanjutnya unlock setelah complete level sebelumnya
- Best score dan best time otomatis update jika ada score/time lebih baik
- Admin hanya bisa VIEW, tidak bisa EDIT/DELETE progress user
