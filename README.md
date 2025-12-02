# Lumen App Batasanaya - Hijaiyyah Learning Game API

[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Lumen](https://img.shields.io/badge/Lumen-10.0-orange.svg)](https://lumen.laravel.com)
[![JWT](https://img.shields.io/badge/JWT-Auth-green.svg)](https://github.com/tymondesigns/jwt-auth)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)](https://mysql.com)

API backend lengkap untuk aplikasi pembelajaran Hijaiyyah dengan sistem autentikasi JWT, manajemen konten multimedia, admin dashboard, dan game pasang huruf interaktif.

## 🚀 Features

### 🔐 Authentication & Security
- **Complete JWT Authentication** - Login, register, refresh token, logout
- **OTP Verification** - 6-digit OTP dengan expiry 5 menit
- **Role-Based Access Control** - Admin & User permissions
- **Admin Login System** - Dedicated admin authentication
- **UUID Primary Keys** - Better security & scalability

### 📁 Content Management
- **Asset Management** - Upload, edit, search, filter by category/subcategory, batch download
- **Song Management** - Upload audio files dengan thumbnail, edit title/file/thumbnail
- **Video Management** - Store video URLs (YouTube/Vimeo) dengan optional thumbnail
- **Background Management** - Upload background images dengan active/inactive status
- **Letter Pairs Management** - Upload huruf Hijaiyyah untuk game
- **Asset Manifest** - Untuk sync game/app data dengan checksum

### 🎮 Game Features
- **Game Cari Hijaiyyah** - Game mencari huruf Hijaiyyah dengan 15 level
- **Game Pasangkan Huruf** - Game pasangkan huruf Hijaiyyah dengan 15 level
- **Progress Tracking** - Tracking completion status dan attempts per level dengan unlock system
- **Session History** - Menyimpan history setiap gameplay (level & timestamp)
- **Simplified Tracking** - Backend hanya track completion, game logic di mobile app
- **Admin Monitoring** - Admin dapat melihat progress kedua game untuk semua user

### 🎛️ Admin Dashboard
- **Professional Admin Panel** - Web-based admin interface
- **Statistics Dashboard** - Real-time stats dan analytics
- **User Management** - CRUD operations untuk users dengan game progress
- **Game Progress Monitoring** - Lihat detail progress kedua game (Cari Hijaiyyah & Pasangkan Huruf) dengan tabs
- **Content Management** - Upload dan kelola semua konten dengan edit/search/filter
- **Asset Search & Filter** - Search by filename, filter by type/category/subcategory
- **Batch Upload** - Upload sampai 50 files sekaligus (max 100MB per file)
- **Background Status** - Active/Inactive status untuk backgrounds
- **Drag & Drop Upload** - Modern file upload interface
- **Responsive Design** - Mobile-friendly admin panel

### 🔧 Technical Features
- **RESTful API Design** - Clean dan consistent endpoints
- **File Validation** - Type, size, dan format validation
- **Error Handling** - Comprehensive error responses
- **CORS Support** - Cross-origin resource sharing
- **Public & Protected Routes** - Flexible access control

## 📋 Requirements

- PHP 8.1+ (8.4 recommended)
- MySQL 8.0+
- Composer
- Nginx/Apache
- PHP Extensions: php-fpm, php-mysql, php-mbstring, php-xml
- PHP Settings: upload_max_filesize=100M, post_max_size=100M, max_file_uploads=50
- Laravel Valet (optional)
- Ngrok (untuk public testing)

## 🛠️ Installation

1. **Clone repository:**
```bash
git clone <repository-url>
cd lumen_app_batasanaya
```

2. **Install dependencies:**
```bash
composer install
```

3. **Setup environment:**
```bash
cp .env.example .env
# Edit .env dengan database credentials
```

4. **Run migrations & seeders:**
```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
```

5. **Create upload folders:**
```bash
mkdir -p public/uploads/{assets,profiles,songs,thumbnails}
chmod -R 755 public/uploads storage
```

6. **Run server:**
```bash
# Via Valet
valet link

# Via PHP built-in server
php -S localhost:8000 -t public
```

## 🌐 Public Access (Ngrok)

```bash
# Install ngrok
yay -S ngrok  # Arch Linux

# Setup auth token
ngrok config add-authtoken YOUR_TOKEN

# Expose local server
ngrok http lumen-app-batasanaya.test:80
```

## 📚 API Documentation

### Base URL
```
Local: http://lumen-app-batasanaya.test
Public: https://your-ngrok-url.ngrok.io
```

### Authentication

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
    "fullName": "John Doe",
    "email": "john@example.com",
    "phone_number": "081234567890",
    "password": "password123"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### Protected Endpoints
Semua endpoint yang membutuhkan autentikasi harus menyertakan header:
```http
Authorization: Bearer YOUR_JWT_TOKEN
```

### User Management

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer YOUR_JWT_TOKEN
```

#### Update User
```http
PATCH /api/users/{id}
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
    "fullName": "Updated Name",
    "email": "updated@example.com"
}
```

#### Upload Profile Picture
```http
POST /api/user/profile-picture
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: multipart/form-data

profile_picture: [file]
```

### Asset Management

#### Upload Asset
```http
POST /api/assets
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: multipart/form-data

file: [file]
type: image|audio
```

#### Get Assets List
```http
GET /api/assets
Authorization: Bearer YOUR_JWT_TOKEN
```

#### Download Single Asset
```http
GET /api/assets/{id}/file
# Public endpoint - no auth required
```

#### Download All Assets (ZIP)
```http
GET /api/assets/download-all
# Public endpoint - no auth required
# Optional: ?type=image or ?type=audio
```

#### Download Batch Assets (ZIP)
```http
POST /api/assets/download-batch
# Public endpoint - no auth required
Content-Type: application/json

{
    "asset_ids": ["uuid1", "uuid2", "uuid3"]
}
```

#### Get Assets Manifest
```http
GET /api/assets/manifest?type=image
# Public endpoint - no auth required
```

**Response:**
```json
{
    "version": 1699123456,
    "total_assets": 10,
    "total_size": 5242880,
    "assets": [
        {
            "id": "uuid",
            "filename": "image.jpg",
            "type": "image",
            "size": 1024000,
            "checksum": "md5hash",
            "download_url": "https://your-url/api/assets/uuid/file",
            "last_modified": 1699123456
        }
    ]
}
```

### Song Management

#### Upload Song
```http
POST /api/songs
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: multipart/form-data

title: Song Title
file: [audio file]
thumbnail: [image file]
```

#### Get Song
```http
GET /api/songs/{id}
Authorization: Bearer YOUR_JWT_TOKEN
```

#### Stream Song
```http
GET /api/songs/{id}/file
Authorization: Bearer YOUR_JWT_TOKEN
```

### OTP System

#### Request OTP
```http
POST /api/auth/request-otp
Authorization: Bearer YOUR_JWT_TOKEN
```

#### Verify OTP
```http
POST /api/auth/verify-otp
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
    "otp": "123456"
}
```

### Game Cari Hijaiyyah API

#### Get Progress (15 Levels)
```http
GET /api/carihijaiyah/progress
Authorization: Bearer YOUR_JWT_TOKEN
```

**Response:**
```json
{
  "levels": [
    {
        "level_number": 1,
        "is_unlocked": true,
        "is_completed": true,
        "attempts": 2
    }
  ]
}
```

#### Start Game
```http
POST /api/carihijaiyah/start
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
    "level_number": 1
}
```

**Response:**
```json
{
    "message": "Game started",
    "level_number": 1,
    "attempts": 3
}
```

#### Finish Game
```http
POST /api/carihijaiyah/finish
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
    "session_id": "uuid",
    "level_number": 1
}
```

**Response:**
```json
{
    "message": "Level completed!",
    "level_number": 1,
    "next_level_unlocked": true
}
```

#### Get Stats
```http
GET /api/carihijaiyah/stats
Authorization: Bearer YOUR_JWT_TOKEN
```

**Response:**
```json
{
    "total_levels_completed": 5,
    "total_sessions": 8
}
```

## 🎮 Game Integration

### Game Cari Hijaiyyah Flow
1. **User requests progress** - GET /api/carihijaiyah/progress
2. **Check unlocked levels** - Level 1 auto-unlocked, others unlock after completing previous
3. **Start game** - POST /api/carihijaiyah/start dengan level_number (get session_id)
4. **Play game** - Game logic handled by mobile app (matching, scoring, validation)
5. **Finish game** - POST /api/carihijaiyah/finish dengan session_id & level_number
6. **Backend updates** - Save session timestamp, mark completed, unlock next level
7. **Admin monitors** - Admin dapat lihat progress semua user via dashboard

### Game Features
- **15 Levels** - Progressive difficulty (both games)
- **Unlock System** - Complete level to unlock next
- **Simplified Tracking** - Backend only tracks completion status & attempts
- **Session History** - All gameplay sessions saved with timestamp
- **Attempts Counter** - Track how many times user tried each level
- **Game Logic in App** - Scoring, stars, time tracking handled by mobile app

### Admin Monitoring
- View all users with both games progress summary (completion count)
- Click user to see detailed progress with tabs (Cari Hijaiyyah & Pasangkan Huruf)
- Each tab shows 15 levels progress + recent 10 sessions
- See stats: completion rate, total sessions per game
- Identify stuck users and difficult levels

## 🗄️ Database Schema

### Authentication Tables
- `roles` - System roles (admin, user)
- `users` - User data with role relations
- `otps` - OTP verification system
- `user_preferences` - User settings
- `user_subscriptions` - Subscription data

### Content Management Tables
- `assets` - General file management
- `songs` - Audio files with thumbnails
- `videos` - Video files with thumbnails
- `backgrounds` - Background images

### Game Tables
- `carihijaiyah_progress` - Progress tracking per level per user (15 levels)
- `carihijaiyah_sessions` - History setiap gameplay session
- `pasangkanhuruf_progress` - Progress tracking per level per user (15 levels)
- `pasangkanhuruf_sessions` - History setiap gameplay session

### Key Relationships
```sql
-- Users have roles
users.role_id -> roles.id

-- Content belongs to users
assets.created_by -> users.id
songs.created_by -> users.id
videos.created_by -> users.id
backgrounds.created_by -> users.id

-- Game progress belongs to users
carihijaiyah_progress.user_id -> users.id
pasangkanhuruf_progress.user_id -> users.id

-- Game sessions belong to users
carihijaiyah_sessions.user_id -> users.id
pasangkanhuruf_sessions.user_id -> users.id
```

## 🔐 Security Features

- JWT token authentication
- Role-based access control
- OTP verification system
- File type validation
- Password hashing (bcrypt)
- CORS protection
- UUID primary keys

## 🎛️ Admin Dashboard

### Access Admin Panel
1. **Login URL:** `http://lumen-backend-batasanaya.test/admin/login`
2. **Default Credentials:**
   - Email: `admin@example.com`
   - Password: `admin123`

### Dashboard Features
- **Statistics Overview** - Real-time stats cards (users, assets, songs, videos, backgrounds, storage)
- **User Management** - Create, edit, delete users dengan game progress monitoring
- **Game Progress Modal** - View detailed 15 level progress + session history per user (dual tabs)
- **Asset Management** - Upload, edit, search, filter files dengan category/subcategory
- **Asset Search & Filter** - Real-time search by filename, filter by type/category/subcategory
- **Batch Upload** - Upload sampai 50 files sekaligus
- **Song Management** - Upload audio dengan thumbnail, edit title/file/thumbnail
- **Video Management** - Store video URLs (YouTube/Vimeo) dengan optional thumbnail
- **Background Management** - Upload background images dengan active/inactive toggle
- **Drag & Drop Upload** - Modern file upload interface

### Admin API Endpoints
```http
# Admin Login
POST /api/auth/admin-login

# Get Dashboard Stats
GET /api/admin/stats
Authorization: Bearer ADMIN_TOKEN

# Get All Users (with game progress)
GET /api/admin/users
Authorization: Bearer ADMIN_TOKEN

# Get User Game Progress Detail
GET /api/admin/users/{userId}/game-progress
Authorization: Bearer ADMIN_TOKEN

# Get All Assets (with search & filter)
GET /api/admin/assets?search=filename&type=image&category=game&subcategory=carihijaiyah
Authorization: Bearer ADMIN_TOKEN

# Bulk Delete Assets
DELETE /api/admin/assets/bulk
Authorization: Bearer ADMIN_TOKEN
Content-Type: application/json

{
    "asset_ids": ["uuid1", "uuid2"]
}
```

## 📁 File Structure

```
public/uploads/
├── assets/        - General file uploads
├── profiles/      - Profile pictures
├── songs/         - Audio files
├── thumbnails/    - Song thumbnails
├── videos/        - Video files
├── backgrounds/   - Background images
└── letter_pairs/  - Hijaiyyah letter images

resources/views/
├── admin/
│   ├── dashboard.php  - Admin panel interface
│   └── login.php      - Admin login page
├── docs/
│   └── index.php      - API documentation
└── layouts/
    └── app.php        - Base layout template

public/
├── css/
│   └── app.css        - Admin panel styles
└── js/
    └── app.js         - Admin panel JavaScript
```

## 🎮 Game/App Integration

### Asset Sync Workflow
1. App requests manifest: `GET /api/assets/manifest`
2. Compare checksums with local files
3. Download batch updates: `POST /api/assets/download-batch`
4. Extract ZIP and replace files

### Example Implementation
```javascript
// Frontend asset sync
const API_BASE = 'https://your-ngrok-url.ngrok.io/api';

// Get manifest
const manifest = await fetch(`${API_BASE}/assets/manifest?type=image`);
const data = await manifest.json();

// Check for updates
const outdatedAssets = data.assets.filter(asset => 
    !localAssets[asset.id] || 
    localAssets[asset.id].checksum !== asset.checksum
);

// Download batch
if (outdatedAssets.length > 0) {
    const response = await fetch(`${API_BASE}/assets/download-batch`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            asset_ids: outdatedAssets.map(a => a.id)
        })
    });
    
    const blob = await response.blob();
    // Extract and update local files
}
```

## 📱 Flutter Integration

### Dependencies (pubspec.yaml)
```yaml
dependencies:
  http: ^1.1.0
  archive: ^3.4.0
  path_provider: ^2.1.1
```

### Asset Download Service
```dart
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:archive/archive.dart';
import 'package:path_provider/path_provider.dart';

class AssetDownloadService {
  static const String baseUrl = 'https://your-ngrok-url.ngrok.io/api';
  
  // Download all assets (no auth required)
  Future<void> downloadAllAssets({String? type}) async {
    try {
      // 1. Download ZIP
      final zipUrl = '$baseUrl/assets/download-all${type != null ? '?type=$type' : ''}';
      final response = await http.get(Uri.parse(zipUrl));
      
      if (response.statusCode == 200) {
        // 2. Save ZIP to temporary
        final tempDir = await getTemporaryDirectory();
        final zipFile = File('${tempDir.path}/assets.zip');
        await zipFile.writeAsBytes(response.bodyBytes);
        
        // 3. Extract ZIP to documents
        await extractAssets(zipFile);
        
        // 4. Cleanup ZIP
        await zipFile.delete();
        
        print('Assets downloaded successfully!');
      }
    } catch (e) {
      print('Error downloading assets: $e');
    }
  }
  
  // Extract ZIP to documents folder
  Future<void> extractAssets(File zipFile) async {
    final appDir = await getApplicationDocumentsDirectory();
    final assetsDir = Directory('${appDir.path}/assets');
    
    if (!await assetsDir.exists()) {
      await assetsDir.create(recursive: true);
    }
    
    final bytes = await zipFile.readAsBytes();
    final archive = ZipDecoder().decodeBytes(bytes);
    
    for (final file in archive) {
      if (file.isFile) {
        final data = file.content as List<int>;
        final extractedFile = File('${assetsDir.path}/${file.name}');
        await extractedFile.parent.create(recursive: true);
        await extractedFile.writeAsBytes(data);
      }
    }
  }
  
  // Load image from local storage
  Future<ImageProvider> loadAssetImage(String filename) async {
    final appDir = await getApplicationDocumentsDirectory();
    final imagePath = '${appDir.path}/assets/image/$filename';
    final imageFile = File(imagePath);
    
    if (await imageFile.exists()) {
      return FileImage(imageFile);
    }
    
    return AssetImage('assets/placeholder.png');
  }
}
```

### Usage in Flutter App
```dart
class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  final AssetDownloadService _downloadService = AssetDownloadService();
  String _status = 'Checking assets...';
  
  @override
  void initState() {
    super.initState();
    _initializeAssets();
  }
  
  Future<void> _initializeAssets() async {
    try {
      setState(() => _status = 'Downloading Hijaiyyah assets...');
      
      // Download all image assets (no login required)
      await _downloadService.downloadAllAssets(type: 'image');
      
      setState(() => _status = 'Assets ready!');
      
      // Navigate to main app
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => MainApp()),
      );
      
    } catch (e) {
      setState(() => _status = 'Error: $e');
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 20),
            Text(_status),
          ],
        ),
      ),
    );
  }
}
```

### Asset Storage Locations
```
Android: /data/data/com.yourapp/app_flutter/documents/assets/image/
iOS: /Documents/assets/image/
Temp ZIP: /cache/assets.zip (deleted after extract)
```

## 📊 Complete API Endpoints Summary

### Public Endpoints (No Auth Required)
```
GET  /                              - Home page
GET  /docs                          - API documentation
GET  /admin/login                   - Admin login page
GET  /admin                         - Admin dashboard

POST /api/auth/register             - User registration
POST /api/auth/login                - User login
POST /api/auth/admin-login          - Admin login
POST /api/auth/refresh              - Refresh JWT token

GET  /api/assets/{id}/file          - Download asset
GET  /api/assets/download-all       - Download all assets (ZIP)
GET  /api/assets/manifest           - Get assets manifest
POST /api/assets/download-batch     - Download batch assets (ZIP)

GET  /api/backgrounds/{id}/file     - Download background
GET  /api/letter-pairs/{id}/outline - Download letter outline
GET  /api/letter-pairs/{id}/complete - Download letter complete
```

### Protected Endpoints (Auth Required)
```
# User Management
GET    /api/auth/me                 - Get current user
POST   /api/auth/reset-password     - Reset password
POST   /api/auth/request-otp        - Request OTP
POST   /api/auth/verify-otp         - Verify OTP
POST   /api/auth/logout             - Logout
GET    /api/users/{id}              - Get user by ID
PATCH  /api/users/{id}              - Update user
POST   /api/user/profile-picture    - Upload profile picture

# Asset Management
POST   /api/assets                  - Upload asset
POST   /api/assets/batch            - Upload multiple assets
GET    /api/assets                  - Get assets list
PATCH  /api/assets/{id}             - Update asset category/subcategory
DELETE /api/assets/{id}             - Delete asset
DELETE /api/assets/batch            - Delete multiple assets

# Song Management
POST   /api/songs                   - Upload song with thumbnail
GET    /api/songs                   - Get songs list
GET    /api/songs/{id}              - Get song details
PATCH  /api/songs/{id}              - Update song (title/file/thumbnail)
GET    /api/songs/{id}/file         - Stream song
DELETE /api/songs/{id}              - Delete song

# Video Management
POST   /api/videos                  - Store video URL with optional thumbnail
GET    /api/videos                  - Get videos list
GET    /api/videos/{id}             - Get video details
PATCH  /api/videos/{id}             - Update video (title/url/thumbnail)
GET    /api/videos/{id}/file        - Get video URL
DELETE /api/videos/{id}             - Delete video

# Background Management
POST   /api/backgrounds             - Upload background
GET    /api/backgrounds             - Get backgrounds list
GET    /api/backgrounds/{id}        - Get background details
PATCH  /api/backgrounds/{id}        - Update background (name, is_active)
DELETE /api/backgrounds/{id}        - Delete background

# Game Cari Hijaiyyah API
GET    /api/carihijaiyah/progress   - Get user progress (15 levels)
POST   /api/carihijaiyah/start      - Start game session
POST   /api/carihijaiyah/finish     - Finish game & save session
GET    /api/carihijaiyah/stats      - Get user stats

# Game Pasangkan Huruf API
GET    /api/pasangkanhuruf/progress - Get user progress (15 levels)
POST   /api/pasangkanhuruf/start    - Start game session
POST   /api/pasangkanhuruf/finish   - Finish game & save session
GET    /api/pasangkanhuruf/stats    - Get user stats

# User Preferences
GET    /api/user/preference         - Get user preferences
PATCH  /api/user/preference         - Update preferences
```

### Admin Only Endpoints
```
GET    /api/admin/stats                      - Dashboard statistics
GET    /api/admin/users                      - Get all users with game progress
GET    /api/admin/users/{userId}/game-progress - Get detailed user game progress
GET    /api/admin/assets                     - Get all assets
DELETE /api/admin/assets/bulk                - Bulk delete assets

POST   /api/users                            - Create user (admin)
DELETE /api/users/{id}                       - Delete user (admin)
```

## 📝 Documentation

- **API Documentation**: Import `BaTaTsaNaYa API.postman_collection.json` untuk testing endpoints
- **Game Cari Hijaiyyah**: Import `Game_Cari_Hijaiyyah_API.postman_collection.json` untuk testing game endpoints
- **Admin Dashboard**: Access via `/admin` with credentials admin@example.com / admin123

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

MIT License - see LICENSE file for details.
