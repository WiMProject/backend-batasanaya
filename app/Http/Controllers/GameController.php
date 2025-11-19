<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\GameMatch;
use App\Models\LetterPair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GameController extends Controller
{
    public function startGame(Request $request)
    {
        $this->validate($request, [
            'difficulty_level' => 'integer|min:1|max:5',
            'total_pairs' => 'integer|min:3|max:20'
        ]);

        $difficultyLevel = $request->difficulty_level ?? 1;
        $totalPairs = $request->total_pairs ?? 6;

        // Get random letter pairs for the game
        $letterPairs = LetterPair::where('is_active', true)
            ->where('difficulty_level', $difficultyLevel)
            ->inRandomOrder()
            ->limit($totalPairs)
            ->get();

        if ($letterPairs->count() < $totalPairs) {
            return response()->json([
                'error' => 'Not enough letter pairs available for this difficulty level'
            ], 400);
        }

        $gameSession = GameSession::create([
            'user_id' => Auth::id(),
            'total_pairs' => $totalPairs
        ]);

        // Shuffle pairs for game
        $gameData = $letterPairs->map(function ($pair) {
            return [
                'pair_id' => $pair->id,
                'letter_name' => $pair->letter_name,
                'outline_url' => $pair->outline_url,
                'complete_url' => $pair->complete_url
            ];
        });

        return response()->json([
            'game_session_id' => $gameSession->id,
            'total_pairs' => $totalPairs,
            'difficulty_level' => $difficultyLevel,
            'letter_pairs' => $gameData
        ]);
    }

    public function submitMatch(Request $request)
    {
        $this->validate($request, [
            'game_session_id' => 'required|uuid|exists:game_sessions,id',
            'letter_pair_id' => 'required|uuid|exists:letter_pairs,id',
            'is_correct' => 'required|boolean'
        ]);

        $gameSession = GameSession::findOrFail($request->game_session_id);
        
        if ($gameSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        GameMatch::create([
            'game_session_id' => $request->game_session_id,
            'letter_pair_id' => $request->letter_pair_id,
            'is_correct' => $request->is_correct,
            'attempt_time' => Carbon::now()
        ]);

        // Update session stats
        if ($request->is_correct) {
            $gameSession->correct_matches++;
        } else {
            $gameSession->wrong_matches++;
        }
        $gameSession->save();

        return response()->json([
            'message' => 'Match recorded',
            'correct_matches' => $gameSession->correct_matches,
            'wrong_matches' => $gameSession->wrong_matches
        ]);
    }

    public function finishGame(Request $request)
    {
        $this->validate($request, [
            'game_session_id' => 'required|uuid|exists:game_sessions,id',
            'time_taken' => 'required|integer|min:1'
        ]);

        $gameSession = GameSession::findOrFail($request->game_session_id);
        
        if ($gameSession->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Calculate score
        $correctPercentage = ($gameSession->correct_matches / $gameSession->total_pairs) * 100;
        $timeBonus = max(0, 300 - $request->time_taken); // Bonus for speed
        $score = ($correctPercentage * 10) + $timeBonus;

        $gameSession->update([
            'time_taken' => $request->time_taken,
            'score' => $score,
            'completed_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Game completed',
            'final_score' => $score,
            'correct_matches' => $gameSession->correct_matches,
            'wrong_matches' => $gameSession->wrong_matches,
            'time_taken' => $request->time_taken,
            'accuracy' => round($correctPercentage, 2) . '%'
        ]);
    }

    public function getLeaderboard()
    {
        $leaderboard = GameSession::with('user')
            ->whereNotNull('completed_at')
            ->orderBy('score', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($session) {
                return [
                    'user_name' => $session->user->full_name,
                    'score' => $session->score,
                    'accuracy' => round(($session->correct_matches / $session->total_pairs) * 100, 2),
                    'time_taken' => $session->time_taken,
                    'completed_at' => $session->completed_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json($leaderboard);
    }
}