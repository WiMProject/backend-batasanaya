<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * AssetController - TESTING/PLAYGROUND VERSION
 * Versi simple dengan fitur minimal untuk testing
 */
class AssetControllerTesting extends Controller
{
    /**
     * 1. UPLOAD BANYAK SEKALIGUS (Batch Upload)
     * Structure: assets/{category}/{subcategory}/{type}/filename
     */
    public function uploadBatch(Request $request)
    {
        $this->validate($request, [
            'files' => 'required|array|max:100',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp3,wav,ogg,json|max:1048576', // 1GB
            'category' => 'required|string',
            'subcategory' => 'nullable|string'
        ]);
        
        $uploaded = [];
        $updated = [];
        
        foreach ($request->file('files') as $file) {
            $fileName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Auto-detect type dari extension
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $type = 'image';
            } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                $type = 'audio';
            } elseif ($extension === 'json') {
                $type = 'lottie';
            } else {
                $type = 'other';
            }
            
            // Build folder path: assets/{category}/{subcategory}/{type}/
            $folderPath = 'assets/' . $request->category;
            if ($request->subcategory) {
                $folderPath .= '/' . $request->subcategory;
            }
            $folderPath .= '/' . $type;
            
            // Create directory if not exists
            $uploadPath = base_path('public/uploads/' . $folderPath);
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            
            $filePath = $uploadPath . '/' . $fileName;
            
            // Check if file exists (update scenario)
            $existingAsset = Asset::where('file_name', $fileName)
                ->where('category', $request->category)
                ->where('subcategory', $request->subcategory)
                ->where('type', $type)
                ->first();
            
            // Move file (replace if exists)
            $file->move($uploadPath, $fileName);
            
            // Generate checksum
            $checksum = md5_file($filePath);
            
            if ($existingAsset) {
                // Update existing asset
                $existingAsset->update([
                    'size' => filesize($filePath),
                    'updated_at' => now()
                ]);
                
                $existingAsset->checksum = $checksum;
                $updated[] = $existingAsset;
            } else {
                // Create new asset
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
        }
        
        return response()->json([
            'message' => count($uploaded) . ' new files uploaded, ' . count($updated) . ' files updated',
            'uploaded' => $uploaded,
            'updated' => $updated
        ], 201);
    }
    
    /**
     * 2. DELETE BANYAK SEKALIGUS (Batch Delete)
     */
    public function deleteBatch(Request $request)
    {
        $this->validate($request, [
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'string|exists:assets,id'
        ]);
        
        $assets = Asset::whereIn('id', $request->asset_ids)->get();
        $deleted = 0;
        
        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            
            $asset->delete();
            $deleted++;
        }
        
        return response()->json([
            'message' => $deleted . ' assets deleted',
            'deleted_count' => $deleted
        ]);
    }
    
    /**
     * 3. CHECKSUM (Generate MD5 untuk semua assets)
     */
    public function getChecksum(Request $request)
    {
        $type = $request->get('type'); // image, audio, atau all
        
        $query = Asset::select('id', 'file_name', 'type', 'file');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get()->map(function($asset) {
            $filePath = base_path('public/' . $asset->file);
            
            return [
                'id' => $asset->id,
                'filename' => $asset->file_name,
                'type' => $asset->type,
                'checksum' => File::exists($filePath) ? md5_file($filePath) : null
            ];
        });
        
        return response()->json([
            'total' => $assets->count(),
            'assets' => $assets
        ]);
    }
    
    /**
     * 4. UPDATE ASSET (Update category/subcategory)
     */
    public function updateAsset(Request $request, $id)
    {
        $asset = Asset::find($id);
        
        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
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
            'message' => 'Asset updated',
            'asset' => $asset
        ]);
    }
    
    /**
     * 5. DOWNLOAD ALL (Download semua assets dalam ZIP)
     */
    public function downloadAll(Request $request)
    {
        $type = $request->get('type'); // image, audio, atau all
        
        $query = Asset::query();
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $assets = $query->get();
        
        if ($assets->isEmpty()) {
            return response()->json(['error' => 'No assets found'], 404);
        }
        
        // Create ZIP
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
}
