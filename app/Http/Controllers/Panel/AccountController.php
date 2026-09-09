<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit()
    {
        return view('panel.account.edit');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', 'different:current_password',
                                   Password::min(8)->letters()->numbers()],
        ], [
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            $message = 'Password saat ini salah.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors'  => ['current_password' => [$message]],
                ], 422);
            }

            return back()->withErrors(['current_password' => $message]);
        }

        $user->update(['password' => $request->input('password')]); // di-hash oleh cast 'hashed'

        $request->session()->regenerate();

        // Sesi lain (browser/device lain) ikut logout; sesi ini tetap hidup.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $message = 'Password berhasil diganti.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
