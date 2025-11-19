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
            'file' => 'required|file|mimes:mp3,wav,m4a|max:10240', // Max 10MB
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(base_path('public/uploads/songs'), $fileName);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnail->move(base_path('public/uploads/thumbnails'), $thumbnailName);
            $thumbnailPath = 'uploads/thumbnails/' . $thumbnailName;
        }

        $song = Song::create([
            'id' => Str::uuid(),
            'title' => $request->title,
            'file' => 'uploads/songs/' . $fileName,
            'thumbnail' => $thumbnailPath,
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
            'title' => 'sometimes|required|string|max:255',
            'file' => 'sometimes|file|mimes:mp3,wav,m4a|max:10240',
            'thumbnail' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->has('title')) {
            $song->title = $request->title;
        }

        if ($request->hasFile('file')) {
            // Delete old file
            $oldFilePath = base_path('public/' . $song->file);
            if (File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }

            // Upload new file
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(base_path('public/uploads/songs'), $fileName);
            $song->file = 'uploads/songs/' . $fileName;
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($song->thumbnail) {
                $oldThumbnailPath = base_path('public/' . $song->thumbnail);
                if (File::exists($oldThumbnailPath)) {
                    File::delete($oldThumbnailPath);
                }
            }

            // Upload new thumbnail
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnail->move(base_path('public/uploads/thumbnails'), $thumbnailName);
            $song->thumbnail = 'uploads/thumbnails/' . $thumbnailName;
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

        // Delete files
        $filePath = base_path('public/' . $song->file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        if ($song->thumbnail) {
            $thumbnailPath = base_path('public/' . $song->thumbnail);
            if (File::exists($thumbnailPath)) {
                File::delete($thumbnailPath);
            }
        }

        $song->delete();
        return response()->json(['message' => 'Song deleted successfully']);
    }
}