<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferenceController extends Controller
{
    /**
     * [TB-30] Mengambil atau membuat user preference.
     */
    public function show()
    {
        $preference = UserPreference::firstOrCreate(
            ['id' => Auth::id()], 
            [ 
                'audio_enabled' => true,
                'music_enabled' => true,
                'max_screen_time' => 7200,
            ]
        );

        return response()->json($preference);
    }

    /**
     * [TB-31] Mengupdate user preference.
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'audio_enabled' => 'sometimes|boolean',
            'music_enabled' => 'sometimes|boolean',
            'max_screen_time' => 'sometimes|integer|min:0',
        ]);

        $preference = UserPreference::firstOrCreate(['id' => Auth::id()]);

        if ($request->has('audio_enabled')) {
            $preference->audio_enabled = $request->audio_enabled;
        }
        if ($request->has('music_enabled')) {
            $preference->music_enabled = $request->music_enabled;
        }
        if ($request->has('max_screen_time')) {
            $preference->max_screen_time = $request->max_screen_time;
        }

        $preference->save();

        return response()->json(['message' => 'Preferensi berhasil diupdate.', 'preference' => $preference]);
    }
}