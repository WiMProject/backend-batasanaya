<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\CariHijaiyyahProgress;
use App\Models\CariHijaiyyahSession;
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
            $progress = CariHijaiyyahProgress::where('user_id', $user->id)
                ->select(
                    DB::raw('COUNT(*) as total_levels'),
                    DB::raw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_levels'),
                    DB::raw('SUM(stars) as total_stars'),
                    DB::raw('SUM(best_score) as total_score')
                )
                ->first();
            
            $user->game_progress = [
                'completed_levels' => $progress->completed_levels ?? 0,
                'total_levels' => $progress->total_levels ?? 0,
                'total_stars' => $progress->total_stars ?? 0,
                'total_score' => $progress->total_score ?? 0
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
        
        // Get all 17 levels progress
        $progress = CariHijaiyyahProgress::where('user_id', $userId)
            ->orderBy('level_number')
            ->get();
        
        // Get recent sessions (last 10)
        $sessions = CariHijaiyyahSession::where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
        
        // Calculate stats
        $stats = [
            'total_completed' => $progress->where('is_completed', true)->count(),
            'total_stars' => $progress->sum('stars'),
            'total_score' => $progress->sum('best_score'),
            'total_attempts' => $progress->sum('attempts'),
            'total_sessions' => CariHijaiyyahSession::where('user_id', $userId)->count(),
            'avg_accuracy' => $sessions->avg(function($s) {
                $total = $s->correct_matches + $s->wrong_matches;
                return $total > 0 ? ($s->correct_matches / $total) * 100 : 0;
            })
        ];
        
        return response()->json([
            'user' => $user,
            'progress' => $progress,
            'recent_sessions' => $sessions,
            'stats' => $stats
        ]);
    }

    /**
     * Daftar semua asset dengan detail uploader
     */
    public function getAllAssets()
    {
        $assets = Asset::with('createdBy')->latest()->paginate(20);
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