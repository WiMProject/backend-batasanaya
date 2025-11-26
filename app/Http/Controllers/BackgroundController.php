<?php

namespace App\Http\Controllers;

use App\Models\Background;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackgroundController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:jpeg,jpg,png|max:20480', // Max 20MB
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $fileSize = $file->getSize();
        
        $file->move(base_path('public/uploads/backgrounds'), $fileName);
        $filePath = 'uploads/backgrounds/' . $fileName;

        $background = Background::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'file' => $filePath,
            'size' => $fileSize,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Background uploaded successfully', 'background' => $background], 201);
    }

    public function index()
    {
        $backgrounds = Background::with('createdBy')->latest()->paginate(15);
        return response()->json($backgrounds);
    }

    public function show($id)
    {
        $background = Background::with('createdBy')->find($id);
        if (!$background) {
            return response()->json(['error' => 'Background not found'], 404);
        }
        return response()->json($background);
    }

    public function download($id)
    {
        $background = Background::find($id);
        if (!$background) {
            return response()->json(['error' => 'Background not found'], 404);
        }

        $filePath = base_path('public/' . $background->file);
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Background file not found'], 404);
        }

        return response()->file($filePath);
    }

    public function update(Request $request, $id)
    {
        $background = Background::find($id);
        if (!$background) {
            return response()->json(['error' => 'Background not found'], 404);
        }

        $this->validate($request, [
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $background->update($request->only(['name', 'is_active']));
        return response()->json(['message' => 'Background updated successfully', 'background' => $background]);
    }

    public function destroy($id)
    {
        $background = Background::find($id);
        if (!$background) {
            return response()->json(['error' => 'Background not found'], 404);
        }

        $filePath = base_path('public/' . $background->file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $background->delete();
        return response()->json(['message' => 'Background deleted successfully']);
    }
}