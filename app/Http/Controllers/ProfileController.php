<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->except('whatsapp_opt_in'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // A data marca QUANDO a escolha do WhatsApp mudou, entao so e
        // tocada quando ela realmente muda (UC10-HU04).
        $optIn = $request->boolean('whatsapp_opt_in');

        if ($optIn !== (bool) $user->whatsapp_opt_in) {
            $user->whatsapp_opt_in = $optIn;
            $user->whatsapp_opt_in_at = $optIn ? now() : null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Sem isso a estetica poderia ficar sem nenhum administrador.
        if ($user->isAdmin()) {
            return back()->withErrors([
                'password' => 'Contas de administrador nao podem ser excluidas por aqui.',
            ], 'userDeletion');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        // D-07: anonimiza em vez de apagar, para os agendamentos ficarem.
        $user->anonymize();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
