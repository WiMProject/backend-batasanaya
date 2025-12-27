<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Upload batch assets dengan category/subcategory structure
     */
    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'files' => 'required|array|max:50',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp3,wav,ogg|max:104857600',
            'category' => 'required|string',
            'subcategory' => 'nullable|string'
        ]);
        
        $uploaded = [];
        $updated = [];
        
        foreach ($request->file('files') as $file) {
            $fileName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Auto-detect type
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $type = 'image';
            } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                $type = 'audio';
            } else {
                continue; // Skip unsupported files
            }
            
            // Build path: assets/{category}/{subcategory}/{type}/
            $folderPath = 'assets/' . $request->category;
            if ($request->subcategory) {
                $folderPath .= '/' . $request->subcategory;
            }
            $folderPath .= '/' . $type;
            
            $uploadPath = base_path('public/uploads/' . $folderPath);
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            
            $filePath = $uploadPath . '/' . $fileName;
            $file->move($uploadPath, $fileName);
            $checksum = md5_file($filePath);
            
            $asset = Asset::create([
                'id' => Str::uuid(),
                'file_name' => $fileName,
                'type' => $type,
                'file' => 'uploads/' . $folderPath . '/' . $fileName,
                'size' => filesize($filePath),
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'created_by_id' => Auth::id(),
            ]);
            $asset->checksum = $checksum;
            $uploaded[] = $asset;
        }
        
        return response()->json([
            'message' => count($uploaded) . ' new files uploaded, ' . count($updated) . ' files updated',
            'uploaded' => $uploaded,
            'updated' => $updated
        ], 201);
    }
    
    public function index()
    {
        $assets = Asset::with('createdBy')->latest()->paginate(20);
        return response()->json($assets);
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        $this->validate($request, [
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,mp3,wav,ogg|max:104857600'
        ]);

        $oldCategory = $asset->category;
        $oldSubcategory = $asset->subcategory;
        $categoryChanged = false;

        // Update category/subcategory
        if ($request->has('category')) {
            $asset->category = $request->category;
            $categoryChanged = ($oldCategory !== $request->category || $oldSubcategory !== $request->subcategory);
        }
        if ($request->has('subcategory')) {
            $asset->subcategory = $request->subcategory;
            $categoryChanged = ($oldCategory !== $asset->category || $oldSubcategory !== $request->subcategory);
        }

        // Replace file if uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Auto-detect type
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $type = 'image';
            } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                $type = 'audio';
            } else {
                return response()->json(['error' => 'Unsupported file type'], 400);
            }

            // Delete old file
            $oldFilePath = base_path('public/' . $asset->file);
            if (File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }

            // Build new path
            $folderPath = 'assets/' . $asset->category;
            if ($asset->subcategory) {
                $folderPath .= '/' . $asset->subcategory;
            }
            $folderPath .= '/' . $type;

            $uploadPath = base_path('public/uploads/' . $folderPath);
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $fileName = $file->getClientOriginalName();
            $file->move($uploadPath, $fileName);

            $asset->file_name = $fileName;
            $asset->type = $type;
            $asset->file = 'uploads/' . $folderPath . '/' . $fileName;
            $asset->size = filesize($uploadPath . '/' . $fileName);
        }
        // Move file if category/subcategory changed (without new file upload)
        elseif ($categoryChanged) {
            $oldFilePath = base_path('public/' . $asset->file);
            
            if (File::exists($oldFilePath)) {
                // Build new path
                $folderPath = 'assets/' . $asset->category;
                if ($asset->subcategory) {
                    $folderPath .= '/' . $asset->subcategory;
                }
                $folderPath .= '/' . $asset->type;

                $uploadPath = base_path('public/uploads/' . $folderPath);
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0755, true);
                }

                $newFilePath = $uploadPath . '/' . $asset->file_name;
                
                // Move file
                File::move($oldFilePath, $newFilePath);
                
                $asset->file = 'uploads/' . $folderPath . '/' . $asset->file_name;
            }
        }

        $asset->save();

        return response()->json([
            'message' => 'Asset updated successfully',
            'asset' => $asset
        ]);
    }

    public function download($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        $filePath = base_path('public/' . $asset->file);
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Asset file not found'], 404);
        }

        return response()->file($filePath);
    }

    public function downloadAll(Request $request)
    {
        $type = $request->get('type');
        $query = Asset::query();
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get();
        if ($assets->isEmpty()) {
            return response()->json(['error' => 'No assets found'], 404);
        }

        $zipFileName = 'all_assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                $zip->addFile($filePath, $asset->file_name);
            }
        }
        
        $zip->close();
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function downloadBatch(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|string|exists:assets,id'
        ]);

        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        if ($assets->isEmpty()) {
            return response()->json(['error' => 'No assets found'], 404);
        }

        $zipFileName = 'assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                $zip->addFile($filePath, $asset->file_name);
            }
        }
        
        $zip->close();
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function manifest(Request $request)
    {
        $type = $request->get('type');
        $query = Asset::select('id', 'file_name', 'type', 'size', 'updated_at');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get()->map(function($asset) {
            $filePath = base_path('public/uploads/assets/' . $asset->file_name);
            return [
                'id' => $asset->id,
                'filename' => $asset->file_name,
                'type' => $asset->type,
                'size' => $asset->size,
                'checksum' => File::exists($filePath) ? md5_file($filePath) : null,
                'download_url' => url('/api/assets/download-all'),
                'last_modified' => $asset->updated_at->timestamp
            ];
        });

        return response()->json([
            'version' => time(),
            'total_assets' => $assets->count(),
            'total_size' => $assets->sum('size'),
            'assets' => $assets
        ]);
    }

    public function destroy($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        $filePath = base_path('public/' . $asset->file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $asset->delete();
        return response()->json(['message' => 'Asset deleted']);
    }

    public function destroyBatch(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|string|exists:assets,id'
        ]);

        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $deletedCount = 0;

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $asset->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => $deletedCount . ' assets deleted',
            'deleted_count' => $deletedCount
        ]);
    }
}
