<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index()
    {
        $rows = User::orderBy('name')->paginate(20);
        return view('admin.users.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            'is_admin' => ['sometimes','boolean'], // falls du zusätzlich zum Spatie-Role-Flag noch dieses Feld nutzt
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => $r->boolean('is_admin'),
        ]);

        if ($r->filled('role')) { $user->syncRoles([$r->input('role')]); }


        // Spatie-Rolle optional setzen
        if (method_exists($user, 'assignRole') && $r->filled('role')) {
            $user->syncRoles([$r->input('role')]);
        }

        return redirect()->route('users.index')->with('success', 'Benutzer angelegt.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','confirmed','min:8'],
            'is_admin' => ['sometimes','boolean'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_admin'] = $r->boolean('is_admin');

        $user->update($data);

        if (method_exists($user, 'assignRole')) {
            $user->syncRoles($r->filled('role') ? [$r->input('role')] : []);
        }

        return back()->with('success', 'Benutzer aktualisiert.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Eigenes Konto kann nicht gelöscht werden.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Benutzer gelöscht.');
    }
}
