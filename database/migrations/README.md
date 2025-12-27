# Clean Migrations - Batasanaya

Folder ini berisi **versi clean** dari migrations yang sudah dirapikan dan digabungkan.

## 📁 Struktur Migrations

### 1. `2025_01_01_000001_create_auth_tables.php`
**Authentication System**
- `roles` - System roles (admin, user)
- `users` - User accounts dengan role
- `otps` - OTP verification system

### 2. `2025_01_01_000002_create_user_related_tables.php`
**User Related Tables**
- `user_preferences` - User settings (audio, music, screen time)
- `user_subscriptions` - Email subscriptions

### 3. `2025_01_01_000003_create_content_tables.php`
**Content Management**
- `assets` - General file uploads (images, audio) dengan category/subcategory
- `songs` - Audio files dengan thumbnail
- `videos` - Video URLs dengan qualities
- `backgrounds` - Background images dengan is_active status

### 4. `2025_01_01_000004_create_carihijaiyah_tables.php`
**Game Cari Hijaiyyah**
- `carihijaiyah_progress` - Progress tracking 15 levels per user
- `carihijaiyah_sessions` - History setiap gameplay

### 5. `2025_01_01_000005_create_pasangkanhuruf_tables.php`
**Game Pasangkan Huruf**
- `pasangkanhuruf_progress` - Progress tracking 15 levels per user
- `pasangkanhuruf_sessions` - History setiap gameplay

## 🚀 Cara Pakai

### Fresh Install (Database Baru)
```bash
# 1. Backup migrations lama
mv database/migrations database/migrations_old

# 2. Copy migrations clean
cp -r database/migrations_clean database/migrations

# 3. Drop database (jika ada)
php artisan db:wipe

# 4. Run migrations
php artisan migrate

# 5. Seed data
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
```

### Testing (Tanpa Replace)
```bash
# Test migrations di database terpisah
# Edit .env, ganti DB_DATABASE ke database testing
php artisan migrate --path=database/migrations_clean
```

## ✨ Keuntungan Clean Migrations

1. **Lebih Sedikit File** - 5 files vs 16 files
2. **Logis Grouping** - Dikelompokkan berdasarkan fungsi
3. **Tidak Ada Modify** - Semua field langsung final, tidak ada migration modify
4. **Mudah Dibaca** - Struktur jelas per kategori
5. **Fresh Start** - Cocok untuk production deployment

## ⚠️ Perbedaan dengan Migrations Lama

### Yang Dihapus
- Field `best_score`, `best_time`, `stars` di progress tables (tidak dipakai)
- Field `score`, `time_taken`, `correct_matches`, `wrong_matches`, `stars` di sessions tables
- Multiple modify migrations yang bikin pusing

### Yang Digabung
- `create_assets_table` + `add_category_to_assets_table` + `modify_category_nullable` → Jadi 1 file
- `create_backgrounds_table` + `add_is_active_to_backgrounds` → Jadi 1 file
- `create_carihijaiyah_tables` + remove unused fields → Jadi 1 file (clean)
- `create_pasangkanhuruf_tables` + remove unused fields → Jadi 1 file (clean)

## 📊 Database Schema Final

```
roles (id, name)
  └── users (id, full_name, email, phone_number, password, role_id)
       ├── otps (id, phone_number, code, is_used, expired_at)
       ├── user_preferences (id=user_id, audio_enabled, music_enabled, max_screen_time)
       ├── user_subscriptions (id, email, user_id)
       ├── assets (id, file_name, type, category, subcategory, file, size, created_by_id)
       ├── songs (id, title, file, created_by_id)
       ├── videos (id, title, file, qualities, created_by_id)
       ├── backgrounds (id, name, file, size, is_active, created_by_id)
       ├── carihijaiyah_progress (id, user_id, level_number, is_unlocked, is_completed, attempts)
       ├── carihijaiyah_sessions (id, user_id, level_number, completed_at)
       ├── pasangkanhuruf_progress (id, user_id, level_number, is_unlocked, is_completed, attempts)
       └── pasangkanhuruf_sessions (id, user_id, level_number, completed_at)
```

## 🔄 Rollback ke Migrations Lama

Jika ada masalah:
```bash
# Restore migrations lama
rm -rf database/migrations
mv database/migrations_old database/migrations
```

## 📝 Notes

- Migrations lama tetap ada di `database/migrations/` (tidak dihapus)
- Clean migrations ada di `database/migrations_clean/`
- Pilih salah satu untuk dipakai, jangan campur!
- Untuk production baru, pakai clean migrations
- Untuk project existing, tetap pakai migrations lama
