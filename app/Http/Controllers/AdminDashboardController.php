<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\Song;
use App\Models\Video;
use App\Models\Background;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function checkAuth()
    {
        // This will be called by JavaScript to check if user needs to login
        return response()->json(['redirect' => '/admin/login']);
    }

    public function getStats()
    {
        $stats = [
            'users' => User::count(),
            'assets' => Asset::count(),
            'images' => Asset::where('type', 'image')->count(),
            'audio' => Asset::where('type', 'audio')->count(),
            'songs' => Song::count(),
            'videos' => Video::count(),
            'backgrounds' => Background::count(),
            'storage_mb' => round((Asset::sum('size') + Background::sum('size')) / 1024 / 1024, 2),
            'recent_users' => User::with('role')->latest()->take(5)->get(),
            'recent_assets' => Asset::with('createdBy')->latest()->take(5)->get(),
            'recent_songs' => Song::with('createdBy')->latest()->take(3)->get(),
            'recent_videos' => Video::with('createdBy')->latest()->take(3)->get(),
            'recent_backgrounds' => Background::with('createdBy')->latest()->take(3)->get()
        ];

        return response()->json($stats);
    }
}