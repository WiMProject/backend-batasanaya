<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:mp4,avi,mov|max:51200', // Max 50MB
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(base_path('public/uploads/videos'), $fileName);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnail->move(base_path('public/uploads/thumbnails'), $thumbnailName);
            $thumbnailPath = 'uploads/thumbnails/' . $thumbnailName;
        }

        $video = Video::create([
            'id' => Str::uuid(),
            'title' => $request->title,
            'file' => 'uploads/videos/' . $fileName,
            'thumbnail' => $thumbnailPath,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Video uploaded successfully', 'video' => $video], 201);
    }

    public function index()
    {
        $videos = Video::with('createdBy')->latest()->paginate(15);
        return response()->json($videos);
    }

    public function show($id)
    {
        $video = Video::with('createdBy')->find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }
        return response()->json($video);
    }

    public function stream($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        $filePath = base_path('public/' . $video->file);
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Video file not found'], 404);
        }

        return response()->file($filePath);
    }

    public function destroy($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        // Delete files
        $filePath = base_path('public/' . $video->file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        if ($video->thumbnail) {
            $thumbnailPath = base_path('public/' . $video->thumbnail);
            if (File::exists($thumbnailPath)) {
                File::delete($thumbnailPath);
            }
        }

        $video->delete();
        return response()->json(['message' => 'Video deleted successfully']);
    }
}