# Admin Guide - Lumen Backend Batasanaya

## 🔐 Admin Authentication

### Admin Login (Quick Access)
```bash
POST /api/auth/admin-login
```

**Response:**
```json
{
    "message": "Admin login successful",
    "user": {
        "id": "uuid",
        "full_name": "Admin User",
        "email": "admin@example.com",
        "role": {
            "name": "admin"
        }
    },
    "access_token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 3600
}
```

**Default Admin Credentials:**
- Email: `admin@example.com`
- Password: `admin123`

## 📊 Admin Dashboard

### Get Dashboard Statistics
```bash
GET /api/admin/dashboard
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
    "total_users": 1,
    "total_assets": 0,
    "total_images": 0,
    "total_audio": 0,
    "total_size_bytes": 0,
    "total_size_mb": 0
}
```

## 👥 User Management

### Get All Users
```bash
GET /api/admin/users
Authorization: Bearer {jwt_token}
```

**Response:** Paginated list of all users with roles

### Create New User (Admin Only)
```bash
POST /api/users
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "fullName": "New User",
    "email": "user@example.com",
    "phone_number": "081234567890",
    "password": "password123",
    "role": "user"
}
```

### Delete User (Admin Only)
```bash
DELETE /api/users/{user_id}
Authorization: Bearer {jwt_token}
```

## 📁 Asset Management

### Get All Assets (Admin View)
```bash
GET /api/admin/assets
Authorization: Bearer {jwt_token}
```

**Response:** Paginated list of all assets with uploader details

### Bulk Delete Assets
```bash
DELETE /api/admin/assets/bulk
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "asset_ids": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
    "message": "3 assets berhasil dihapus oleh admin.",
    "deleted_count": 3
}
```

### Regular Asset Operations
All regular asset endpoints work for admin:

- `POST /api/assets` - Upload single asset
- `POST /api/assets/batch` - Upload multiple assets
- `GET /api/assets` - List assets
- `DELETE /api/assets/{id}` - Delete single asset

## 🎵 Content Management

### Song Management
- `POST /api/songs` - Upload song with thumbnail
- `GET /api/songs/{id}` - Get song details
- `DELETE /api/songs/{id}` - Delete song

## 🔧 Testing with cURL

### 1. Login as Admin
```bash
curl -X POST http://lumen-backend-batasanaya.test/api/auth/admin-login \
  -H "Content-Type: application/json"
```

### 2. Get Dashboard (use token from step 1)
```bash
curl -X GET http://lumen-backend-batasanaya.test/api/admin/dashboard \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 3. Upload Asset
```bash
curl -X POST http://lumen-backend-batasanaya.test/api/assets \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "file=@/path/to/your/file.jpg"
```

### 4. Bulk Delete Assets
```bash
curl -X DELETE http://lumen-backend-batasanaya.test/api/admin/assets/bulk \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"asset_ids": ["uuid1", "uuid2"]}'
```

## 🛡️ Security Features

1. **Role-Based Access Control**: Admin endpoints protected by `role:admin` middleware
2. **JWT Authentication**: All admin operations require valid JWT token
3. **Admin-Only Operations**: 
   - User creation/deletion
   - Bulk asset operations
   - System statistics access

## 📱 Admin Panel Integration

For frontend admin panel, use these endpoints:

1. **Login Flow**: Use `/api/auth/admin-login` for quick admin access
2. **Dashboard**: Display statistics from `/api/admin/dashboard`
3. **User Management**: CRUD operations via user endpoints
4. **Asset Management**: Upload, view, and bulk delete assets
5. **Content Management**: Manage songs and other media

## 🔄 Regular vs Admin Access

**Regular User Can:**
- Upload assets to their account
- View their own assets
- Download public assets
- Manage their profile

**Admin Can:**
- All regular user operations
- View all users and assets
- Delete any user/asset
- Bulk operations
- System statistics
- Create users with specific roles

## 🚀 Production Notes

1. **Change Default Password**: Update admin password in production
2. **JWT Secret**: Use strong JWT secret key
3. **Rate Limiting**: Consider adding rate limiting for admin endpoints
4. **Audit Logging**: Log admin operations for security
5. **Backup**: Regular database backups for asset metadata