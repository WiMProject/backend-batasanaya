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
     * [TB-25] Menyimpan file asset baru.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:jpg,jpeg,png,mp3,wav|max:5120',
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        
        // Simpan dengan nama asli, tambah counter jika duplicate
        $fileName = $originalName;
        $counter = 1;
        while (file_exists(base_path('public/uploads/assets/' . $fileName))) {
            $info = pathinfo($originalName);
            $fileName = $info['filename'] . '_' . $counter . '.' . $info['extension'];
            $counter++;
        }
        
        $fileSize = $file->getSize();
        
        $extension = strtolower($file->getClientOriginalExtension());
        $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'audio';

        $file->move(base_path('public/uploads/assets'), $fileName);
        $filePath = 'uploads/assets/' . $fileName;

        $asset = Asset::create([
            'id' => Str::uuid(),
            'file_name' => $fileName,
            'type' => $type,
            'file' => $filePath,
            'size' => $fileSize,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'created_by_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Asset berhasil di-upload.', 'asset' => $asset], 201);
    }

    /**
     * Upload multiple assets sekaligus
     */
    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'files' => 'required|array|max:50', // Max 50 files
            'files.*' => 'required|file|mimes:jpg,jpeg,png,mp3,wav|max:5120',
            'category' => 'nullable|string',
            'subcategory' => 'nullable|string'
        ]);

        $uploadedAssets = [];
        $skippedFiles = [];
        $files = $request->file('files');
        $usedFileNames = []; // Track filenames dalam batch ini

        foreach ($files as $file) {
            // Validasi per file
            $fileSize = $file->getSize();
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExts = ['jpg', 'jpeg', 'png', 'mp3', 'wav'];
            
            // Skip jika tidak valid
            if (!in_array($extension, $allowedExts) || $fileSize > 5120 * 1024) {
                $skippedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'reason' => !in_array($extension, $allowedExts) ? 'Invalid format' : 'File too large (max 5MB)'
                ];
                continue;
            }
            $originalName = $file->getClientOriginalName();
            
            // Simpan dengan nama asli, tambah counter jika duplicate
            $fileName = $originalName;
            $counter = 1;
            while (file_exists(base_path('public/uploads/assets/' . $fileName)) || in_array($fileName, $usedFileNames)) {
                $info = pathinfo($originalName);
                $fileName = $info['filename'] . '_' . $counter . '.' . $info['extension'];
                $counter++;
            }
            
            $usedFileNames[] = $fileName; // Tandai filename sudah dipakai
            
            $fileSize = $file->getSize();
            
            // Auto-detect type berdasarkan extension
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' : 'audio';

            // Pindahkan file ke folder public/uploads/assets
            $file->move(base_path('public/uploads/assets'), $fileName);
            $filePath = 'uploads/assets/' . $fileName;

            $asset = Asset::create([
                'id' => Str::uuid(),
                'file_name' => $fileName,
                'type' => $type,
                'file' => $filePath,
                'size' => $fileSize,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'created_by_id' => Auth::id(),
            ]);

            $uploadedAssets[] = $asset;
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
     * [TB-37] Mengambil semua data asset dengan paginasi.
     */
    public function index()
    {
        $assets = Asset::with('createdBy')->latest()->paginate(20);

        return response()->json($assets);
    }

    /**
     * Update asset (category and subcategory)
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
     * download/tampil asset.
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

        // Mengembalikan file langsung
        return response()->file($filePath);
    }

    /**
     * Download batch assets (untuk game/app data)
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

        // Buat ZIP file
        $zipFileName = 'assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Gagal membuat ZIP file.'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                // Buat path dengan category/subcategory
                $folderPath = '';
                if ($asset->category) {
                    $folderPath .= $asset->category . '/';
                    if ($asset->subcategory) {
                        $folderPath .= $asset->subcategory . '/';
                    }
                }
                
                $zip->addFile($filePath, $folderPath . $asset->file_name);
            }
        }
        
        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Download all assets (ZIP)
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
            return response()->json(['error' => 'Tidak ada assets untuk didownload.'], 404);
        }

        // Buat ZIP file
        $zipFileName = 'all_assets_' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'Gagal membuat ZIP file.'], 500);
        }

        foreach ($assets as $asset) {
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                // Buat path dengan category/subcategory
                $folderPath = '';
                if ($asset->category) {
                    $folderPath .= $asset->category . '/';
                    if ($asset->subcategory) {
                        $folderPath .= $asset->subcategory . '/';
                    }
                }
                
                $zip->addFile($filePath, $folderPath . $asset->file_name);
            }
        }
        
        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Get assets manifest (untuk sync game data)
     */
    public function manifest(Request $request)
    {
        $type = $request->get('type'); // image, audio, atau all
        
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
     * [TB-38] Menghapus asset.
     */
    public function destroy($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json(['error' => 'Asset tidak ditemukan.'], 404);
        }

        // Hapus file fisiknya dari folder public
        $filePath = base_path('public/' . $asset->file);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Hapus record dari database
        $asset->delete();

        return response()->json(['message' => 'Asset berhasil dihapus.']);
    }

    /**
     * Delete multiple assets sekaligus
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
            // Hapus file fisiknya dari folder public
            $filePath = base_path('public/' . $asset->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            // Hapus record dari database
            $asset->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => $deletedCount . ' assets berhasil dihapus.',
            'deleted_count' => $deletedCount
        ]);
    }
}
