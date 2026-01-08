<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * [TB-17] Mendaftarkan user baru.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => ['required','string','unique:users,phone_number','regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $userRole = Role::where('name', 'user')->first();
        if (!$userRole) {
            return response()->json(['error' => 'Konfigurasi error: Default user role not found.'], 500);
        }

        $user = User::create([
            'id' => Str::uuid(),
            'full_name' => $request->fullName,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role_id' => $userRole->id,
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * [TB-18] Login user.
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized: Email atau password salah.'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * [TB-19] Mengganti password user yang sedang login.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        if ($request->old_password === $request->new_password) {
            return response()->json([
                'new_password' => ['Password baru tidak boleh sama dengan password lama.']
            ], 422);
        }
        
        $user = User::find(Auth::id());

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Password lama salah.'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    /**
     * [TB-New] Set password baru tanpa old password (untuk Forgot Password flow).
     */
    public function setNewPassword(Request $request)
    {
        $this->validate($request, [
            'new_password' => 'required|string|min:6'
        ]);
        
        $user = User::find(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil direset. Silakan login kembali dengan password baru.']);
    }

    /**
     * [TB-20] Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $newToken = Auth::refresh();

        return $this->respondWithToken($newToken);
    }

    /**
     * [TB-21a] Membuat dan mengirim OTP baru.
     */
    public function requestOtp(Request $request)
    {
        $user = Auth::user();

        // Jika user belum login (Lupa Password flow), cari berdasarkan email
        if (!$user) {
            $this->validate($request, [
                'email' => 'required|email'
            ]);
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['error' => 'Email tidak terdaftar.'], 404);
            }
        }

        // Memastikan user punya nomor telepon terdaftar
        if (!$user->phone_number) {
            return response()->json(['error' => 'Nomor telepon tidak ditemukan untuk user ini.'], 400);
        }

        // Buat kode OTP 6 digit acak
        $otpCode = mt_rand(100000, 999999);

        // Buat OTP baru di database
        \App\Models\Otp::create([
            'id' => Str::uuid(),
            'phone_number' => $user->phone_number,
            'code' => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(5), // OTP valid selama 5 menit
        ]);

        return response()->json([
            'message' => 'OTP berhasil dikirim.',
            'phone_number' => $user->phone_number, // Info ke user dikirim kemana
            // Hanya untuk testing development
            'testing_otp_code' => $otpCode,
        ]);
    }

    /**
     * [TB-21b] Memverifikasi OTP yang dikirim user.
     */
    public function verifyOtp(Request $request)
    {
        $this->validate($request, [
            'otp' => 'required|string|digits:6',
            'email' => 'nullable|email' // Optional jika sudah login
        ]);

        $user = Auth::user();

        // Jika belum login, cari user berdasarkan email
        if (!$user) {
            if (!$request->email) {
                return response()->json(['error' => 'Email wajib diisi untuk verifikasi tanpa login.'], 400);
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['error' => 'User tidak ditemukan.'], 404);
            }
        }

        $submittedOtp = $request->otp;

        // Cari OTP yang valid di database
        $otp = \App\Models\Otp::where('phone_number', $user->phone_number)
            ->where('is_used', false)
            ->where('is_revoked', false)
            ->latest()
            ->first();

        // Cek jika tidak ada OTP yang valid atau kodenya salah
        if (!$otp || $otp->code !== $submittedOtp) {
            return response()->json(['error' => 'Kode OTP tidak valid.'], 400);
        }

        // Cek jika OTP sudah kedaluwarsa
        if (Carbon::now()->gt($otp->expired_at)) {
            $otp->is_revoked = true;
            $otp->save();
            return response()->json(['error' => 'Kode OTP sudah kedaluwarsa.'], 400);
        }

        // Jika semua berhasil, tandai OTP sudah dipakai
        $otp->is_used = true;
        $otp->save();

        // Jika user belum login, berikan token login agar bisa reset password
        if (!Auth::check()) {
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'OTP berhasil diverifikasi. Anda sekarang login.',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        }

        return response()->json(['message' => 'OTP berhasil diverifikasi.']);
    }

    /**
     * [TB-22] Logout user (invalidate the token).
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * [TB-29] GET data user yang sedang login.
     */
    public function getMe()
    {
        $user = User::with('role')->find(Auth::id());

        return response()->json($user);
    }

    /**
     * Admin login dengan kredensial default
     */
    public function adminLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Admin credentials invalid.'], 401);
        }

        $user = User::with('role')->where('email', $request->email)->first();
        
        if (!$user || $user->role->name !== 'admin') {
            Auth::logout();
            return response()->json(['error' => 'Not authorized as admin.'], 403);
        }
        
        return response()->json([
            'message' => 'Admin login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
     
    /**
     * Format response dengan token JWT.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
}