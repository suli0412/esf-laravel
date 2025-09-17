<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// optional: use Illuminate\Support\Facades\Password;

class UserRoleController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    // NEU: User anlegen + Rollen zuweisen
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'email'  => ['required','email','max:255','unique:users,email'],
            'roles'  => ['array'],
            'roles.*'=> ['string','exists:roles,name'],
        ]);

        $tempPassword = Str::password(12); // z.B. "Y3mPq1..."; Laravel 11/12 hat das
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($tempPassword),
        ]);

        $user->syncRoles($data['roles'] ?? ['Mitarbeiter']); // Default: Mitarbeiter

        // Optional: Reset-Link mailen, wenn Mail konfiguriert ist
        // try { Password::sendResetLink(['email' => $user->email]); } catch (\Throwable $e) {}

        return back()->with('success', "User angelegt. Temp-Passwort: {$tempPassword}");
    }

    public function updateRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string','exists:roles,name'],
        ]);

        $user->syncRoles($data['roles'] ?? []);
        return back()->with('success', 'Rollen aktualisiert.');
    }
}
