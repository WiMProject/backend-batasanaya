<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * [TB-23] Membuat user baru (hanya untuk admin).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'fullName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => ['required','string','unique:users,phone_number','regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,user' 
        ]);

        $role = Role::where('name', $request->role)->first();
        if (!$role) {
            return response()->json(['error' => 'Role tidak valid.'], 400);
        }

        $user = User::create([
            'id' => Str::uuid(),
            'full_name' => $request->fullName,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        return response()->json(['message' => 'User berhasil dibuat.', 'user' => $user], 201);
    }

    /**
     * [TB-24] Mengambil data satu user berdasarkan ID.
     */
    public function show($id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }

        return response()->json($user);
    }

    /**
     * [TB-26] Mengupdate data user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }
        
        $loggedInUser = Auth::user();
        if ($loggedInUser->id !== $user->id && $loggedInUser->role->name !== 'admin') {
            return response()->json(['error' => 'Forbidden. Anda tidak punya hak akses.'], 403);
        }

        $this->validate($request, [
            'fullName' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        ]);

        if ($request->has('fullName')) {
            $user->full_name = $request->fullName;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json(['message' => 'User berhasil diupdate.', 'user' => $user]);
    }

    /**
     * [TB-27] Menghapus user (hanya untuk admin).
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }

        if (Auth::id() === $user->id) {
            return response()->json(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    /**
     * [TB-28] Mengupload atau mengganti foto profil.
     */
    public function uploadProfilePicture(Request $request)
    {
        $this->validate($request, [
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        $user = User::find(Auth::id());
        $file = $request->file('profile_picture');

        $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Cek foto lama 
        if ($user->profile_picture) {
            $oldPicturePath = base_path('public/' . $user->profile_picture);
            if (File::exists($oldPicturePath)) {
                File::delete($oldPicturePath);
            }
        }

        $file->move(base_path('public/uploads/profiles'), $fileName);
        $filePath = 'uploads/profiles/' . $fileName;

        // Simpan ke database 
        $user->profile_picture = $filePath;
        $user->save();

        return response()->json(['message' => 'Foto profil berhasil di-upload.', 'user' => $user]);
    }
}

