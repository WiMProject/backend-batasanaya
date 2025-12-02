<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\CariHijaiyyahProgress;
use App\Models\CariHijaiyyahSession;
use App\Models\PasangkanHurufProgress;
use App\Models\PasangkanHurufSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Dashboard admin - statistik asset dan user
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalAssets = Asset::count();
        $totalImages = Asset::where('type', 'image')->count();
        $totalAudio = Asset::where('type', 'audio')->count();
        $totalSize = Asset::sum('size');

        return response()->json([
            'total_users' => $totalUsers,
            'total_assets' => $totalAssets,
            'total_images' => $totalImages,
            'total_audio' => $totalAudio,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ]);
    }

    /**
     * Daftar semua user untuk admin dengan game progress
     */
    public function getAllUsers()
    {
        $users = User::with('role')
            ->latest()
            ->paginate(20);
        
        // Tambahkan game progress summary untuk setiap user
        $users->getCollection()->transform(function ($user) {
            // Cari Hijaiyyah Progress
            $cariCompleted = CariHijaiyyahProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count();
            
            // Pasangkan Huruf Progress
            $pasangkanCompleted = PasangkanHurufProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count();
            
            $user->game_progress = [
                'carihijaiyah' => [
                    'completed' => $cariCompleted
                ],
                'pasangkanhuruf' => [
                    'completed' => $pasangkanCompleted
                ]
            ];
            
            return $user;
        });
        
        return response()->json($users);
    }
    
    /**
     * Detail game progress user (admin only)
     */
    public function getUserGameProgress($userId)
    {
        $user = User::findOrFail($userId);
        
        // Cari Hijaiyyah
        $cariProgress = CariHijaiyyahProgress::where('user_id', $userId)
            ->orderBy('level_number')
            ->get();
        $cariSessions = CariHijaiyyahSession::where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
        $cariStats = [
            'total_completed' => $cariProgress->where('is_completed', true)->count(),
            'total_sessions' => $cariSessions->count()
        ];
        
        // Pasangkan Huruf
        $pasangkanProgress = PasangkanHurufProgress::where('user_id', $userId)
            ->orderBy('level_number')
            ->get();
        $pasangkanSessions = PasangkanHurufSession::where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
        $pasangkanStats = [
            'total_completed' => $pasangkanProgress->where('is_completed', true)->count(),
            'total_sessions' => $pasangkanSessions->count()
        ];
        
        return response()->json([
            'user' => $user,
            'carihijaiyah' => [
                'progress' => $cariProgress,
                'recent_sessions' => $cariSessions,
                'stats' => $cariStats
            ],
            'pasangkanhuruf' => [
                'progress' => $pasangkanProgress,
                'recent_sessions' => $pasangkanSessions,
                'stats' => $pasangkanStats
            ]
        ]);
    }

    /**
     * Daftar semua asset dengan detail uploader
     */
    public function getAllAssets(Request $request)
    {
        $query = Asset::with('createdBy');
        
        // Search by filename
        if ($request->has('search') && $request->search) {
            $query->where('file_name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter by subcategory
        if ($request->has('subcategory') && $request->subcategory) {
            $query->where('subcategory', $request->subcategory);
        }
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $assets = $query->latest()->paginate(20);
        return response()->json($assets);
    }

    /**
     * Hapus multiple assets sekaligus (admin only)
     */
    public function bulkDeleteAssets(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|string|exists:assets,id'
        ]);

        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $deletedCount = 0;

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $asset->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => $deletedCount . ' assets berhasil dihapus oleh admin.',
            'deleted_count' => $deletedCount
        ]);
    }
}