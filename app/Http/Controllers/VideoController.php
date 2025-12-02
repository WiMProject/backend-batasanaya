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
            'url' => 'required|url|max:500',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

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
            'url' => $request->url,
            'thumbnail' => $thumbnailPath,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Video URL saved successfully', 'video' => $video], 201);
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

    public function update(Request $request, $id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        $this->validate($request, [
            'title' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->has('title')) {
            $video->title = $request->title;
        }

        if ($request->has('url')) {
            $video->url = $request->url;
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($video->thumbnail) {
                $oldThumbPath = base_path('public/' . $video->thumbnail);
                if (File::exists($oldThumbPath)) {
                    File::delete($oldThumbPath);
                }
            }

            // Upload new thumbnail
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnail->move(base_path('public/uploads/thumbnails'), $thumbnailName);
            $video->thumbnail = 'uploads/thumbnails/' . $thumbnailName;
        }

        $video->save();
        return response()->json(['message' => 'Video updated successfully', 'video' => $video]);
    }

    public function stream($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        // Return video URL for redirect
        return response()->json(['url' => $video->url]);
    }

    public function destroy($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        // Delete thumbnail only (URL doesn't need file deletion)
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
