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
        // Allow 10 minutes for video processing
        set_time_limit(600);
        
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:mp4,avi,mov,mkv,ts|max:1024000',
        ]);

        $videoId = Str::uuid();
        $videoFile = $request->file('file');
        
        // Create video folder
        $videoDir = base_path('public/uploads/videos/' . $videoId);
        File::makeDirectory($videoDir, 0755, true);
        
        // Save original
        $originalPath = $videoDir . '/original.mp4';
        $videoFile->move($videoDir, 'original.mp4');
        
        // Generate qualities (360p, 720p, 1080p)
        $qualities = $this->generateQualities($videoId, $originalPath);

        $video = Video::create([
            'id' => $videoId,
            'title' => $request->title,
            'file' => 'uploads/videos/' . $videoId . '/master.m3u8',
            'qualities' => json_encode($qualities),
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Video uploaded successfully', 'video' => $video], 201);
    }
    
    private function generateQualities($videoId, $originalPath)
    {
        // Requires FFmpeg installed
        $videoDir = base_path('public/uploads/videos/' . $videoId);
        $qualities = [];
        
        $resolutions = [
            '380p' => ['width' => 640, 'height' => 380, 'bitrate' => '800k'],
            '480p' => ['width' => 854, 'height' => 480, 'bitrate' => '1200k'],
            '780p' => ['width' => 1280, 'height' => 780, 'bitrate' => '2500k'],
            '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => '5000k'],
        ];
        
        foreach ($resolutions as $quality => $config) {
            $qualityDir = $videoDir . '/' . $quality;
            File::makeDirectory($qualityDir, 0755, true);
            
            // FFmpeg command to generate HLS
            $cmd = sprintf(
                'ffmpeg -i %s -vf scale=%d:%d -c:v libx264 -b:v %s -c:a aac -hls_time 10 -hls_playlist_type vod -hls_segment_filename %s/segment_%%03d.ts %s/playlist.m3u8',
                escapeshellarg($originalPath),
                $config['width'],
                $config['height'],
                $config['bitrate'],
                escapeshellarg($qualityDir),
                escapeshellarg($qualityDir)
            );
            
            exec($cmd);
            
            $qualities[] = [
                'quality' => $quality,
                'url' => url('uploads/videos/' . $videoId . '/' . $quality . '/playlist.m3u8')
            ];
        }
        
        // Create master playlist
        $this->createMasterPlaylist($videoDir, $resolutions);
        
        return $qualities;
    }
    
    private function createMasterPlaylist($videoDir, $resolutions)
    {
        $content = "#EXTM3U\n#EXT-X-VERSION:3\n";
        
        foreach ($resolutions as $quality => $config) {
            $content .= sprintf(
                "#EXT-X-STREAM-INF:BANDWIDTH=%d,RESOLUTION=%dx%d\n%s/playlist.m3u8\n",
                (int)str_replace('k', '000', $config['bitrate']),
                $config['width'],
                $config['height'],
                $quality
            );
        }
        
        file_put_contents($videoDir . '/master.m3u8', $content);
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
            'file' => 'nullable|file|mimes:mp4,avi,mov,mkv,ts|max:1024000',
        ]);

        if ($request->has('title')) {
            $video->title = $request->title;
        }

        if ($request->hasFile('file')) {
            if ($video->file && File::exists(base_path('public/' . $video->file))) {
                File::delete(base_path('public/' . $video->file));
            }
            
            $videoFile = $request->file('file');
            $videoFileName = time() . '_' . $videoFile->getClientOriginalName();
            $videoFile->move(base_path('public/uploads/videos'), $videoFileName);
            $video->file = 'uploads/videos/' . $videoFileName;
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

        if ($video->file && File::exists(base_path('public/' . $video->file))) {
            File::delete(base_path('public/' . $video->file));
        }

        $video->delete();
        return response()->json(['message' => 'Video deleted successfully']);
    }
}
