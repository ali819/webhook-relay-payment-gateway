<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/**
 * Registrasi hanya untuk setup awal: dipakai sekali, saat belum ada satu pun
 * akun admin. Begitu akun pertama dibuat, rute ini menutup diri sendiri.
 */
class RegisterController extends Controller
{
    public function showForm()
    {
        if (User::exists()) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (User::exists()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:190|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Kunci baris: dua request bersamaan tidak boleh sama-sama lolos cek
        // "belum ada user" dan membuat dua admin pertama.
        $user = DB::transaction(function () use ($data) {
            if (User::lockForUpdate()->exists()) {
                return null;
            }

            return User::create($data);
        });

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun admin sudah ada. Silakan login.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel.domains.index');
    }
}
