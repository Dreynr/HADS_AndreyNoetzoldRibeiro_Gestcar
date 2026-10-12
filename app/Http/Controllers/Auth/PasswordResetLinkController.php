<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => 'Nao encontramos uma conta com esse e-mail.',
            ]);
        }

        DB::table('password_reset_codes')->where('email', $request->email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->insert([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        Mail::to($request->email)->send(new PasswordResetCodeMail($code));

        return redirect()->route('password.verify-code.form', ['email' => $request->email])
            ->with('status', 'Enviamos um codigo de 6 digitos para o seu e-mail.');
    }
}
