<?php

namespace App\Http\Controllers;

use App\Models\CariHijaiyyahProgress;
use App\Models\CariHijaiyyahSession;
use App\Models\PasangkanHurufProgress;
use App\Models\PasangkanHurufSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GameController extends Controller
{
    private function getProgressModel($gameType)
    {
        return $gameType === 'carihijaiyah' ? CariHijaiyyahProgress::class : PasangkanHurufProgress::class;
    }
    
    private function getSessionModel($gameType)
    {
        return $gameType === 'carihijaiyah' ? CariHijaiyyahSession::class : PasangkanHurufSession::class;
    }
    
    public function getProgress($gameType)
    {
        $userId = Auth::id();
        $ProgressModel = $this->getProgressModel($gameType);
        
        $progress = [];
        for ($level = 1; $level <= 15; $level++) {
            $levelProgress = $ProgressModel::firstOrCreate(
                ['user_id' => $userId, 'level_number' => $level],
                [
                    'id' => Str::uuid(),
                    'is_unlocked' => $level === 1,
                    'is_completed' => false,
                    'attempts' => 0
                ]
            );
            
            $progress[] = [
                'level_number' => $levelProgress->level_number,
                'is_unlocked' => $levelProgress->is_unlocked,
                'is_completed' => $levelProgress->is_completed,
                'attempts' => $levelProgress->attempts
            ];
        }
        
        return response()->json(['levels' => $progress]);
    }
    
    public function startGame($gameType, Request $request)
    {
        $this->validate($request, [
            'level_number' => 'required|integer|min:1|max:15'
        ]);
        
        $userId = Auth::id();
        $levelNumber = $request->level_number;
        $ProgressModel = $this->getProgressModel($gameType);
        
        $progress = $ProgressModel::where('user_id', $userId)
            ->where('level_number', $levelNumber)
            ->first();
            
        if (!$progress || !$progress->is_unlocked) {
            return response()->json(['error' => 'Level belum unlock'], 403);
        }
        
        $progress->increment('attempts');
        
        $sessionId = Str::uuid();
        
        return response()->json([
            'session_id' => $sessionId,
            'level_number' => $levelNumber,
            'started_at' => Carbon::now()->toDateTimeString()
        ]);
    }
    
    public function finishGame($gameType, Request $request)
    {
        $this->validate($request, [
            'session_id' => 'required|string',
            'level_number' => 'required|integer|min:1|max:15'
        ]);
        
        $userId = Auth::id();
        $ProgressModel = $this->getProgressModel($gameType);
        $SessionModel = $this->getSessionModel($gameType);
        
        $SessionModel::create([
            'id' => $request->session_id,
            'user_id' => $userId,
            'level_number' => $request->level_number,
            'completed_at' => Carbon::now()
        ]);
        
        $progress = $ProgressModel::where('user_id', $userId)
            ->where('level_number', $request->level_number)
            ->first();
            
        if ($progress) {
            $progress->is_completed = true;
            $progress->save();
            
            $nextLevel = $request->level_number + 1;
            if ($nextLevel <= 15) {
                $nextProgress = $ProgressModel::firstOrCreate(
                    ['user_id' => $userId, 'level_number' => $nextLevel],
                    [
                        'id' => Str::uuid(),
                        'is_unlocked' => false,
                        'is_completed' => false,
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
            'next_level_unlocked' => $request->level_number < 15
        ]);
    }
    
    public function getStats($gameType)
    {
        $userId = Auth::id();
        $ProgressModel = $this->getProgressModel($gameType);
        $SessionModel = $this->getSessionModel($gameType);
        
        $totalCompleted = $ProgressModel::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();
            
        $totalSessions = $SessionModel::where('user_id', $userId)->count();
        
        return response()->json([
            'total_levels_completed' => $totalCompleted,
            'total_sessions' => $totalSessions
        ]);
    }
}
