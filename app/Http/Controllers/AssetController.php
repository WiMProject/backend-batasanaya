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
     * Helper to build asset folder path
     */
    private function getAssetPath($category, $subcategory, $type)
    {
        $path = 'assets/' . $category;
        if ($subcategory) {
            $path .= '/' . $subcategory;
        }
        $path .= '/' . $type;
        return $path;
    }

    /**
     * Upload single asset
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:jpg,jpeg,png,mp3,wav,ogg|max:104857600',
            'category' => 'required|string',
            'subcategory' => 'nullable|string'
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Auto-detect type
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $type = 'image';
        } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
            $type = 'audio';
        } else {
            return response()->json(['error' => 'Unsupported file type'], 400);
        }

        $folderPath = $this->getAssetPath($request->category, $request->subcategory, $type);
        $uploadPath = base_path('public/uploads/' . $folderPath);
        
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $filePath = $uploadPath . '/' . $fileName;
        $file->move($uploadPath, $fileName);
        
        $asset = Asset::create([
            'id' => Str::uuid(),
            'file_name' => $fileName,
            'type' => $type,
            'file' => 'uploads/' . $folderPath . '/' . $fileName,
            'size' => filesize($filePath),
            'checksum' => md5_file($filePath),
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json($asset, 201);
    }

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
            
            $folderPath = $this->getAssetPath($request->category, $request->subcategory, $type);
            $uploadPath = base_path('public/uploads/' . $folderPath);
            
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            
            $filePath = $uploadPath . '/' . $fileName;
            $file->move($uploadPath, $fileName);
            
            $asset = Asset::create([
                'id' => Str::uuid(),
                'file_name' => $fileName,
                'type' => $type,
                'file' => 'uploads/' . $folderPath . '/' . $fileName,
                'size' => filesize($filePath),
                'checksum' => md5_file($filePath),
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'created_by_id' => Auth::id(),
            ]);
            
            $uploaded[] = $asset;
        }
        
        return response()->json([
            'message' => count($uploaded) . ' files uploaded successfully',
            'uploaded' => $uploaded
        ], 201);
    }
    
    public function index(Request $request)
    {
        $query = Asset::with('createdBy');
        
        // Search by filename
        if ($request->has('search')) {
            $query->where('file_name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        // Filter by subcategory
        if ($request->has('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }
        
        $assets = $query->latest()->paginate(20);
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
        
        // Update Metadata
        if ($request->has('category')) $asset->category = $request->category;
        if ($request->has('subcategory')) $asset->subcategory = $request->subcategory;

        $pathChanged = ($oldCategory !== $asset->category || $oldSubcategory !== $asset->subcategory);

        // Handle File Replacement
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) $type = 'image';
            elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) $type = 'audio';
            else return response()->json(['error' => 'Unsupported file type'], 400);

            // Delete old file
            $oldFilePath = base_path('public/' . $asset->file);
            if (File::exists($oldFilePath)) File::delete($oldFilePath);

            // New Path
            $folderPath = $this->getAssetPath($asset->category, $asset->subcategory, $type);
            $uploadPath = base_path('public/uploads/' . $folderPath);
            if (!File::exists($uploadPath)) File::makeDirectory($uploadPath, 0755, true);

            $fileName = $file->getClientOriginalName();
            $file->move($uploadPath, $fileName);
            $finalPath = $uploadPath . '/' . $fileName;

            $asset->file_name = $fileName;
            $asset->type = $type;
            $asset->file = 'uploads/' . $folderPath . '/' . $fileName;
            $asset->size = filesize($finalPath);
            $asset->checksum = md5_file($finalPath);

        } elseif ($pathChanged) {
            // Move existing file
            $oldFilePath = base_path('public/' . $asset->file);
            
            if (File::exists($oldFilePath)) {
                $folderPath = $this->getAssetPath($asset->category, $asset->subcategory, $asset->type);
                $uploadPath = base_path('public/uploads/' . $folderPath);
                if (!File::exists($uploadPath)) File::makeDirectory($uploadPath, 0755, true);

                $newFilePath = $uploadPath . '/' . $asset->file_name;
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
        if (!$asset) return response()->json(['error' => 'Asset not found'], 404);

        $filePath = base_path('public/' . $asset->file);
        if (!File::exists($filePath)) return response()->json(['error' => 'Asset file not found'], 404);

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
        if ($assets->isEmpty()) return response()->json(['error' => 'No assets found'], 404);

        $zipFileName = 'all_assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                // Determine internal ZIP structure based on category
                $zipInternalPath = $asset->category;
                if ($asset->subcategory) $zipInternalPath .= '/' . $asset->subcategory;
                $zipInternalPath .= '/' . $asset->type . '/' . $asset->file_name;

                $zip->addFile($filePath, $zipInternalPath);
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
        if ($assets->isEmpty()) return response()->json(['error' => 'No assets found'], 404);

        $zipFileName = 'assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                 $zipInternalPath = $asset->category;
                if ($asset->subcategory) $zipInternalPath .= '/' . $asset->subcategory;
                $zipInternalPath .= '/' . $asset->type . '/' . $asset->file_name;

                $zip->addFile($filePath, $zipInternalPath);
            }
        }
        
        $zip->close();
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function manifest(Request $request)
    {
        $type = $request->get('type');
        $query = Asset::select('id', 'file_name', 'type', 'size', 'updated_at', 'checksum');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        // Optimization: Use DB checksum instead of reading file every time
        $assets = $query->get()->map(function($asset) {
            return [
                'id' => $asset->id,
                'filename' => $asset->file_name,
                'type' => $asset->type,
                'size' => $asset->size,
                'checksum' => $asset->checksum, // Used stored checksum
                'download_url' => url('/api/assets/' . $asset->id . '/file'),
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
        if (!$asset) return response()->json(['error' => 'Asset not found'], 404);

        $filePath = base_path('public/' . $asset->file);
        if (File::exists($filePath)) File::delete($filePath);

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
            if (File::exists($filePath)) File::delete($filePath);
            
            $asset->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => $deletedCount . ' assets deleted',
            'deleted_count' => $deletedCount
        ]);
    }
}
