# Admin Dashboard JavaScript Structure

## File: app.js (Organized)

### 📋 Sections Overview

#### 1. Configuration & Global Variables
- `adminToken` - JWT token storage
- `selectedAssets` - Array for bulk operations
- `API_BASE` - API endpoint configuration
- `NGROK_HEADERS` - Headers for ngrok support

#### 2. Initialization
- `DOMContentLoaded` event listener
- `initAdmin()` - Main initialization
- `initAllUploadZones()` - Setup upload zones

#### 3. Authentication & Token Management
- `initAdmin()` - Initialize admin dashboard
- `verifyTokenAndLoad()` - Verify JWT token
- `adminLogin()` - Admin authentication
- `ensureValidToken()` - Token refresh logic

#### 4. Dashboard & Statistics
- `loadDashboardData()` - Load stats
- `updateStatsCards()` - Update stat cards
- `updateRecentActivity()` - Update recent items
- `loadTabContent()` - Tab switching logic

#### 5. User Management
- `loadUsers()` - Load user list with game progress
- `createUser()` - Create new user (admin)
- `editUser()` - Edit user modal
- `updateUser()` - Update user data
- `deleteUser()` - Delete user (admin)

#### 6. Asset Management
- `loadAssets()` - Load assets with pagination
- `renderAssetsPagination()` - Pagination UI
- `initUploadZone()` - Setup asset upload
- `handleFiles()` - Batch upload (max 50 files)
- `updateSelectedAssets()` - Bulk selection
- `bulkDelete()` - Bulk delete assets
- `editAsset()` - Edit asset metadata
- `updateAsset()` - Update asset
- `deleteAsset()` - Delete single asset

#### 7. Background Management
- `loadBackgrounds()` - Load backgrounds
- `initBackgroundUploadZone()` - Setup upload
- `handleBackgroundFiles()` - Upload handler (max 20MB)
- `uploadBackgroundFile()` - Upload single background
- `editBackground()` - Edit background modal
- `updateBackground()` - Update name & status
- `deleteBackground()` - Delete background

#### 8. Game Progress Monitoring
- `viewGameProgress()` - Display detailed progress modal
  - Shows both games (Cari Hijaiyyah & Pasangkan Huruf)
  - 15 levels per game
  - Stats, progress, and session history

#### 9. Utility Functions
- `formatFileSize()` - Format bytes to readable size
- `showAlert()` - Display toast notifications
- `refreshStats()` - Refresh dashboard stats
- `updateEditSubcategories()` - Dynamic subcategory dropdown

#### 10. Window Exports
- Export functions to global scope for HTML onclick handlers
- `window.editAsset`, `window.updateAsset`, etc.

---

## 🔧 Key Features

### Upload Limits
- Assets: 100MB per file, 50 files max
- Backgrounds: 20MB per file
- Category required before upload

### Authentication
- JWT token with auto-refresh
- Admin role verification
- Redirect to login if unauthorized

### Game Progress
- Dual game support (Cari Hijaiyyah & Pasangkan Huruf)
- 15 levels each
- Progress tracking with stars, score, time
- Session history

### Asset Management
- Batch upload with category/subcategory
- Pagination support
- Bulk delete
- Edit metadata

---

## 📊 Function Count
- Total Functions: ~40+
- Authentication: 4 functions
- Dashboard: 4 functions
- User Management: 5 functions
- Asset Management: 10 functions
- Background Management: 7 functions
- Game Progress: 1 function
- Utilities: 4 functions
- Window Exports: 6+ functions

---

## ✅ Code Quality
- ✅ No syntax errors
- ✅ Organized sections with comments
- ✅ Inline documentation for complex functions
- ✅ Consistent naming conventions
- ✅ Error handling with try-catch
- ✅ User feedback with alerts

---

Generated: $(date)
