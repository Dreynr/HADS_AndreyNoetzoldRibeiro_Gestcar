<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeClientMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // 'accepted' exige que a caixa venha marcada (RNF09, D-08).
            'privacy' => ['accepted'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
        ], [
            'privacy.accepted' => 'E preciso aceitar a politica de privacidade para criar a conta.',
        ]);

        $whatsappOptIn = $request->boolean('whatsapp_opt_in');

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'privacy_accepted_at' => now(),
            'whatsapp_opt_in' => $whatsappOptIn,
            'whatsapp_opt_in_at' => $whatsappOptIn ? now() : null,
        ]);

        event(new Registered($user));

        try {
            Mail::to($user->email)->send(new WelcomeClientMail($user));
        } catch (\Exception $e) {
            report($e);
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
