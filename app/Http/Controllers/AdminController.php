<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Daftar semua user untuk admin
     */
    public function getAllUsers()
    {
        $users = User::with('role')->latest()->paginate(20);
        return response()->json($users);
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