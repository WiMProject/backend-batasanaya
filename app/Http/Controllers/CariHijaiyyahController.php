<?php

namespace App\Http\Controllers;

use App\Models\CariHijaiyyahProgress;
use App\Models\CariHijaiyyahSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CariHijaiyyahController extends Controller
{
    /**
     * Get user progress untuk semua 17 level
     */
    public function getProgress()
    {
        $userId = Auth::id();
        
        // Ambil atau buat progress untuk 17 level
        $progress = [];
        for ($level = 1; $level <= 17; $level++) {
            $levelProgress = CariHijaiyyahProgress::firstOrCreate(
                ['user_id' => $userId, 'level_number' => $level],
                [
                    'id' => Str::uuid(),
                    'is_unlocked' => $level === 1, // Level 1 unlock otomatis
                    'is_completed' => false,
                    'best_score' => 0,
                    'best_time' => 0,
                    'stars' => 0,
                    'attempts' => 0
                ]
            );
            
            $progress[] = [
                'level_number' => $levelProgress->level_number,
                'is_unlocked' => $levelProgress->is_unlocked,
                'is_completed' => $levelProgress->is_completed,
                'best_score' => $levelProgress->best_score,
                'best_time' => $levelProgress->best_time,
                'stars' => $levelProgress->stars,
                'attempts' => $levelProgress->attempts
            ];
        }
        
        return response()->json(['levels' => $progress]);
    }
    
    /**
     * Start game session
     */
    public function startGame(Request $request)
    {
        $this->validate($request, [
            'level_number' => 'required|integer|min:1|max:17'
        ]);
        
        $userId = Auth::id();
        $levelNumber = $request->level_number;
        
        // Cek apakah level sudah unlock
        $progress = CariHijaiyyahProgress::where('user_id', $userId)
            ->where('level_number', $levelNumber)
            ->first();
            
        if (!$progress || !$progress->is_unlocked) {
            return response()->json(['error' => 'Level belum unlock'], 403);
        }
        
        // Increment attempts
        $progress->increment('attempts');
        
        $sessionId = Str::uuid();
        
        return response()->json([
            'session_id' => $sessionId,
            'level_number' => $levelNumber,
            'started_at' => now()->toDateTimeString()
        ]);
    }
    
    /**
     * Finish game dan save score
     */
    public function finishGame(Request $request)
    {
        $this->validate($request, [
            'session_id' => 'required|string',
            'level_number' => 'required|integer|min:1|max:17',
            'score' => 'required|integer|min:0',
            'time_taken' => 'required|integer|min:0',
            'correct_matches' => 'required|integer|min:0',
            'wrong_matches' => 'required|integer|min:0',
            'stars' => 'required|integer|min:1|max:3'
        ]);
        
        $userId = Auth::id();
        
        // Save session
        CariHijaiyyahSession::create([
            'id' => $request->session_id,
            'user_id' => $userId,
            'level_number' => $request->level_number,
            'score' => $request->score,
            'time_taken' => $request->time_taken,
            'correct_matches' => $request->correct_matches,
            'wrong_matches' => $request->wrong_matches,
            'stars' => $request->stars,
            'completed_at' => now()
        ]);
        
        // Update progress
        $progress = CariHijaiyyahProgress::where('user_id', $userId)
            ->where('level_number', $request->level_number)
            ->first();
            
        $isNewBest = false;
        if ($progress) {
            // Update best score jika lebih tinggi
            if ($request->score > $progress->best_score) {
                $progress->best_score = $request->score;
                $isNewBest = true;
            }
            
            // Update best time jika lebih cepat (atau pertama kali)
            if ($progress->best_time == 0 || $request->time_taken < $progress->best_time) {
                $progress->best_time = $request->time_taken;
            }
            
            // Update stars jika lebih tinggi
            if ($request->stars > $progress->stars) {
                $progress->stars = $request->stars;
            }
            
            // Mark as completed
            $progress->is_completed = true;
            $progress->save();
            
            // Unlock next level
            $nextLevel = $request->level_number + 1;
            if ($nextLevel <= 17) {
                $nextProgress = CariHijaiyyahProgress::firstOrCreate(
                    ['user_id' => $userId, 'level_number' => $nextLevel],
                    [
                        'id' => Str::uuid(),
                        'is_unlocked' => false,
                        'is_completed' => false,
                        'best_score' => 0,
                        'best_time' => 0,
                        'stars' => 0,
                        'attempts' => 0
                    ]
                );
                
                if (!$nextProgress->is_unlocked) {
                    $nextProgress->is_unlocked = true;
                    $nextProgress->save();
                }
            }
        }
        
        return response()->json([
            'message' => 'Level completed!',
            'level_number' => $request->level_number,
            'score' => $request->score,
            'time_taken' => $request->time_taken,
            'stars' => $request->stars,
            'is_new_best' => $isNewBest,
            'next_level_unlocked' => $request->level_number < 17
        ]);
    }
    
    /**
     * Get user stats
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalCompleted = CariHijaiyyahProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();
            
        $totalStars = CariHijaiyyahProgress::where('user_id', $userId)
            ->sum('stars');
            
        $totalScore = CariHijaiyyahProgress::where('user_id', $userId)
            ->sum('best_score');
            
        $totalSessions = CariHijaiyyahSession::where('user_id', $userId)->count();
        
        return response()->json([
            'total_levels_completed' => $totalCompleted,
            'total_stars' => $totalStars,
            'total_score' => $totalScore,
            'total_sessions' => $totalSessions
        ]);
    }
}
