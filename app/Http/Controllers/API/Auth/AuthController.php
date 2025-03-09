<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi inputan yang diterima dari request
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ],
            // Kustomisasi pesan error jika validasi tidak sesuai
            [
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!',
                'password.required' => 'Password tidak boleh kosong!',
                'password.min' => 'Password minimal 8 karakter!'
            ]
        );

        // Check validation jika tidak sesuai maka akan mengembalikan response berikut ini
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cek user berdasarkan email yang dimasukkan user sesuai atau tidak dengan yang ada di database
        $user = User::where('email', operator: $request->email)->first();

        // Cek email dan password sesuai atau tidak
        if ($user && Hash::check($request->password, $user->password)) {
            // Jika berhasil login akan dibuat token untuk user tersebut
            $token = $user->createToken('authToken')->plainTextToken;
            // Mengembalikan response berikut ini
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'data' => [
                    'user' => $user
                ],
                'token_type' => 'Bearer',
                'token' => $token
            ], 200);
        } else {
            // Cek email jika tidak ditemukan di database maka akan mengembalikan response berikut ini
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tidak terdaftar!',
                ], 400);
            } else {
                // Cek password jika tidak sesuai dengan yang ada di database maka akan mengembalikan response berikut ini
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Password salah!',
                    ], 400);
                }
            }
        }
    }

    public function register(Request $request)
    {
        // Validasi inputan yang diterima dari request
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|numeric|unique:users,phone',
                'password' => 'required|min:8|confirmed'
            ],
            [
                'name.required' => 'Nama tidak boleh kosong!',
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!',
                'email.unique' => 'Email sudah terdaftar!',
                'phone.required' => 'Nomor telepon tidak boleh kosong!',
                'phone.numeric' => 'Nomor telepon harus berupa angka!',
                'phone.unique' => 'Nomor telepon sudah terdaftar!',
                'password.required' => 'Password tidak boleh kosong!',
                'password.min' => 'Password minimal 8 karakter!',
                'password.confirmed' => 'Konfirmasi password tidak sama!'
            ]
        );

        // Check validation jika tidak sesuai maka akan mengembalikan response berikut ini
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Membuat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        //  Membuat token untuk user yang berhasil register
        $token = $user->createToken('authToken')->plainTextToken;

        // Mengembalikan response berikut ini
        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'Register berhasil!',
                'data' => [
                    'user' => $user
                ],
                'token_type' => 'Bearer',
                'token' => $token
            ], 200);
        } else {
            // Jika gagal register akan mengembalikan response berikut ini
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal register!',
            ], 400);
        }
    }

    public function logout()
    {
        // Proses logout dengan menghapus token yang dimiliki user
        $user = User::where('id', Auth::user()->id)->first();
        $user->tokens()->delete();

        // Mengembalikan response berikut ini
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout!',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        // Validasi inputan yang diterima dari request
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email'
            ],
            // Kustomisasi pesan error jika validasi tidak sesuai
            [
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!'
            ]
        );

        // Check validation jika tidak sesuai maka akan mengembalikan response berikut ini
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Proses pengiriman link reset password ke email user
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Mengembalikan response berikut ini berdasarkan status pengiriman link reset password ke email user
        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'status' => 'success',
                'message' => 'Link reset password telah dikirim ke email!'
            ], 200)
            : response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim link reset password!'
            ], 400);
    }

    public function resetPassword(Request $request)
    {
        // Url reset password yang dikirim ke email user akan mengandung query string token dan email user //
        // Handle GET request //
        // Jika user mengakses link reset password maka akan mengembalikan response berikut ini
        if ($request->isMethod('get')) {
            // Mengembalikan response berikut ini
            return response()->json([
                'status' => 'success',
                'message' => 'Silakan kirim permintaan POST dengan token, email, dan password baru untuk mereset password.',
                // Tampilkan email dan token
                'data' => [
                    'email' => $request->query('email'),
                    'token' => $request->query('token')
                ]
            ], 200);
        }

        // Jika user mengirimkan request POST untuk mereset password maka akan mengembalikan response berikut ini //
        // Handle POST request //
        // Validasi inputan yang diterima dari request
        $validator = Validator::make(
            $request->all(),
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed'
            ],
            // Kustomisasi pesan error jika validasi tidak sesuai
            [
                'token.required' => 'Token tidak boleh kosong!',
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!',
                'password.required' => 'Password tidak boleh kosong!',
                'password.min' => 'Password minimal 8 karakter!',
                'password.confirmed' => 'Konfirmasi password tidak sama!'
            ]
        );

        // Check validation jika tidak sesuai maka akan mengembalikan response berikut ini
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Proses reset password user
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        // Mengembalikan response berikut ini berdasarkan status reset password user
        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'status' => 'success',
                'message' => 'Password berhasil direset!'
            ], 200)
            : response()->json([
                'status' => 'error',
                'message' => 'Gagal mereset password!'
            ], 400);
    }
}
