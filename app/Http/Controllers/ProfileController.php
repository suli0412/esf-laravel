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
    public function edit(Request $request) {
        $user = $request->user();
        return view('profile.edit', compact('user')); // lege einfache View an
    }

    public function update(Request $request) {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
        ]);
        $request->user()->update($data);
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request) {
        $request->validate(['password' => ['required','current_password']]);
        $user = $request->user();
        auth()->logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
