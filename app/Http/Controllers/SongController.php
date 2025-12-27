<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SongController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:mp3,wav,ogg|max:102400',
        ]);

        $audioFile = $request->file('file');
        $audioFileName = time() . '_' . $audioFile->getClientOriginalName();
        $audioFile->move(base_path('public/uploads/songs'), $audioFileName);
        $audioPath = 'uploads/songs/' . $audioFileName;

        $song = Song::create([
            'id' => Str::uuid(),
            'title' => $request->title,
            'file' => $audioPath,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Song uploaded successfully', 'song' => $song], 201);
    }

    public function index()
    {
        $songs = Song::with('createdBy')->latest()->paginate(15);
        return response()->json($songs);
    }

    public function show($id)
    {
        $song = Song::with('createdBy')->find($id);
        if (!$song) {
            return response()->json(['error' => 'Song not found'], 404);
        }
        return response()->json($song);
    }

    public function update(Request $request, $id)
    {
        $song = Song::find($id);
        if (!$song) {
            return response()->json(['error' => 'Song not found'], 404);
        }

        $this->validate($request, [
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:mp3,wav,ogg|max:102400',
        ]);

        if ($request->has('title')) {
            $song->title = $request->title;
        }

        if ($request->hasFile('file')) {
            if ($song->file && File::exists(base_path('public/' . $song->file))) {
                File::delete(base_path('public/' . $song->file));
            }
            
            $audioFile = $request->file('file');
            $audioFileName = time() . '_' . $audioFile->getClientOriginalName();
            $audioFile->move(base_path('public/uploads/songs'), $audioFileName);
            $song->file = 'uploads/songs/' . $audioFileName;
        }

        $song->save();
        return response()->json(['message' => 'Song updated successfully', 'song' => $song]);
    }

    public function stream($id)
    {
        $song = Song::find($id);
        if (!$song) {
            return response()->json(['error' => 'Song not found'], 404);
        }

        $filePath = base_path('public/' . $song->file);
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Song file not found'], 404);
        }

        return response()->file($filePath);
    }

    public function destroy($id)
    {
        $song = Song::find($id);
        if (!$song) {
            return response()->json(['error' => 'Song not found'], 404);
        }

        if ($song->file && File::exists(base_path('public/' . $song->file))) {
            File::delete(base_path('public/' . $song->file));
        }

        $song->delete();
        return response()->json(['message' => 'Song deleted successfully']);
    }
}
