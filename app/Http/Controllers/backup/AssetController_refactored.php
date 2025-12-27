<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    // ============================================
    // HELPER METHODS (Private)
    // ============================================
    
    /**
     * Generate unique filename dengan folder per category/subcategory
     */
    private function generateUniqueFilename($originalName, $category = null, $subcategory = null, $usedNames = [])
    {
        $folderPath = $this->buildStoragePath($category, $subcategory);
        $fileName = $originalName;
        $counter = 1;
        
        // Cek di folder category/subcategory nya aja, bukan global
        while (file_exists($folderPath . $fileName) || in_array($fileName, $usedNames)) {
            $info = pathinfo($originalName);
            $fileName = $info['filename'] . '_' . $counter . '.' . $info['extension'];
            $counter++;
        }
        
        return $fileName;
    }
    
    /**
     * Build storage path berdasarkan category/subcategory
     */
    private function buildStoragePath($category = null, $subcategory = null)
    {
        $path = base_path('public/uploads/assets/');
        
        if ($category) {
            $path .= $category . '/';
            
            if ($subcategory) {
                $path .= $subcategory . '/';
            }
        }
        
        // Buat folder kalau belum ada
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        
        return $path;
    }
    
    /**
     * Detect file type berdasarkan extension
     */
    private function detectFileType($extension)
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png']) ? 'image' : 'audio';
    }
    
    /**
     * Save uploaded file dan create asset record
     */
    private function saveAsset($file, $category = null, $subcategory = null, $usedNames = [])
    {
        $originalName = $file->getClientOriginalName();
        $fileName = $this->generateUniqueFilename($originalName, $category, $subcategory, $usedNames);
        $fileSize = $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension());
        $type = $this->detectFileType($extension);
        
        // Simpan ke folder category/subcategory
        $storagePath = $this->buildStoragePath($category, $subcategory);
        $file->move($storagePath, $fileName);
        
        // Build relative path untuk database
        $relativePath = 'uploads/assets/';
        if ($category) {
            $relativePath .= $category . '/';
            if ($subcategory) {
                $relativePath .= $subcategory . '/';
            }
        }
        $filePath = $relativePath . $fileName;
        
        return Asset::create([
            'id' => Str::uuid(),
            'file_name' => $fileName,
            'type' => $type,
            'file' => $filePath,
            'size' => $fileSize,
            'category' => $category,
            'subcategory' => $subcategory,
            'created_by_id' => Auth::id(),
        ]);
    }
    
    /**
     * Add assets to ZIP dengan folder structure
     */
    private function addAssetsToZip($zip, $assets)
    {
        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            
            if (File::exists($filePath)) {
                $folderPath = $this->buildFolderPath($asset);
                $zip->addFile($filePath, $folderPath . $asset->file_name);
            }
        }
    }
    
    /**
     * Build folder path berdasarkan category/subcategory
     */
    private function buildFolderPath($asset)
    {
        $folderPath = '';
        
        if ($asset->category) {
            $folderPath .= $asset->category . '/';
            
            if ($asset->subcategory) {
                $folderPath .= $asset->subcategory . '/';
            }
        }
        
        return $folderPath;
    }
    
    /**
     * Delete physical file dari storage
     */
    private function deletePhysicalFile($asset)
    {
        $filePath = base_path('public/' . $asset->file);
        
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
    
    /**
     * Create ZIP file dan return path
     */
    private function createZipFile($fileName)
    {
        $zipPath = storage_path('app/' . $fileName);
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Gagal membuat ZIP file.');
        }
        
        return [$zip, $zipPath];
    }
    
    // ============================================
    // PUBLIC METHODS (API Endpoints)
    // ============================================
    
    /**
     * Upload single asset
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:jpg,jpeg,png,mp3,wav|max:102400', // 100MB
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string'
        ]);
        
        $asset = $this->saveAsset(
            $request->file('file'),
            $request->category,
            $request->subcategory
        );
        
        return response()->json([
            'message' => 'Asset berhasil di-upload.',
            'asset' => $asset
        ], 201);
    }
    
    /**
     * Upload multiple assets (batch)
     */
    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'files' => 'required|array|max:100',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,mp3,wav|max:102400',
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string'
        ]);
        
        $uploadedAssets = [];
        $skippedFiles = [];
        $usedFileNames = [];
        
        foreach ($request->file('files') as $file) {
            try {
                $asset = $this->saveAsset(
                    $file,
                    $request->category,
                    $request->subcategory,
                    $usedFileNames
                );
                
                $uploadedAssets[] = $asset;
                $usedFileNames[] = $asset->file_name;
                
            } catch (\Exception $e) {
                $skippedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        $response = [
            'message' => count($uploadedAssets) . ' assets berhasil di-upload.',
            'uploaded_count' => count($uploadedAssets),
            'assets' => $uploadedAssets
        ];
        
        if (!empty($skippedFiles)) {
            $response['skipped_count'] = count($skippedFiles);
            $response['skipped_files'] = $skippedFiles;
        }
        
        return response()->json($response, 201);
    }
    
    /**
     * Get all assets dengan pagination
     */
    public function index()
    {
        $assets = Asset::with('createdBy')->latest()->paginate(20);
        return response()->json($assets);
    }
    
    /**
     * Update asset category/subcategory
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        
        if (!$asset) {
            return response()->json(['error' => 'Asset tidak ditemukan.'], 404);
        }
        
        $this->validate($request, [
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string'
        ]);
        
        $asset->update([
            'category' => $request->category,
            'subcategory' => $request->subcategory
        ]);
        
        return response()->json([
            'message' => 'Asset berhasil diupdate.',
            'asset' => $asset->fresh()
        ]);
    }
    
    /**
     * Download single asset
     */
    public function download($id)
    {
        $asset = Asset::find($id);
        
        if (!$asset) {
            return response()->json(['error' => 'Asset tidak ditemukan.'], 404);
        }
        
        $filePath = base_path('public/' . $asset->file);
        
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File asset tidak ditemukan di server.'], 404);
        }
        
        return response()->file($filePath);
    }
    
    /**
     * Download batch assets (ZIP)
     */
    public function downloadBatch(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|string|exists:assets,id'
        ]);
        
        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        
        if ($assets->isEmpty()) {
            return response()->json(['error' => 'Assets tidak ditemukan.'], 404);
        }
        
        $zipFileName = 'assets_' . time() . '.zip';
        list($zip, $zipPath) = $this->createZipFile($zipFileName);
        
        $this->addAssetsToZip($zip, $assets);
        $zip->close();
        
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Download all assets (ZIP)
     */
    public function downloadAll(Request $request)
    {
        $type = $request->get('type');
        $query = Asset::query();
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get();
        
        if ($assets->isEmpty()) {
            return response()->json(['error' => 'Tidak ada assets untuk didownload.'], 404);
        }
        
        $zipFileName = 'all_assets_' . time() . '.zip';
        list($zip, $zipPath) = $this->createZipFile($zipFileName);
        
        $this->addAssetsToZip($zip, $assets);
        $zip->close();
        
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Get assets manifest untuk sync
     */
    public function manifest(Request $request)
    {
        $type = $request->get('type');
        $query = Asset::select('id', 'file_name', 'type', 'size', 'updated_at');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get()->map(function($asset) {
            return [
                'id' => $asset->id,
                'filename' => $asset->file_name,
                'type' => $asset->type,
                'size' => $asset->size,
                'checksum' => md5_file(base_path('public/uploads/assets/' . $asset->file_name)),
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
    
    /**
     * Delete single asset
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);
        
        if (!$asset) {
            return response()->json(['error' => 'Asset tidak ditemukan.'], 404);
        }
        
        $this->deletePhysicalFile($asset);
        $asset->delete();
        
        return response()->json(['message' => 'Asset berhasil dihapus.']);
    }
    
    /**
     * Delete multiple assets (batch)
     */
    public function destroyBatch(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'required|string|exists:assets,id'
        ]);
        
        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $deletedCount = 0;
        
        foreach ($assets as $asset) {
            $this->deletePhysicalFile($asset);
            $asset->delete();
            $deletedCount++;
        }
        
        return response()->json([
            'message' => $deletedCount . ' assets berhasil dihapus.',
            'deleted_count' => $deletedCount
        ]);
    }
}
