<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function verifyCodeForm(Request $request): View
    {
        return view('auth.verify-code', [
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return back()->withInput()->withErrors([
                'code' => 'Codigo invalido ou expirado. Solicite um novo codigo.',
            ]);
        }

        $request->session()->put('password_reset_email', $request->email);
        $request->session()->put('password_reset_verified', true);

        return redirect()->route('password.reset.form');
    }

    public function create(): View|RedirectResponse
    {
        if (! session('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => session('password_reset_email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! session('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = session('password_reset_email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Usuario nao encontrado.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        DB::table('password_reset_codes')->where('email', $email)->delete();

        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()->route('login')
            ->with('status', 'Senha redefinida com sucesso! Faca login com a nova senha.');
    }
}
