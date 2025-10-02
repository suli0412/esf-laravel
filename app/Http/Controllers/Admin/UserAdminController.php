<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password as PasswordBroker;

class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:users.view'])->only(['index', 'show']);
        $this->middleware(['auth', 'permission:users.manage'])->only(['create', 'store', 'edit', 'update', 'destroy', 'sendReset']);
    }

    /* -------------------------------------------------
     | Liste + Suche
     * ------------------------------------------------*/
    public function index(Request $request)
    {
        $q     = trim($request->get('q', ''));
        $roles = Role::orderBy('name')->pluck('name')->all();

        $users = User::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'roles', 'q'));
    }

    /* -------------------------------------------------
     | Detail (optional)
     * ------------------------------------------------*/
    public function show(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name')->all();
        return view('admin.users.show', compact('user', 'roles'));
    }

    /* -------------------------------------------------
     | Neu anlegen
     * ------------------------------------------------*/
    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name')->all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(\Illuminate\Http\Request $request)
{
    // Passwort-Policy: mind. 10 Zeichen, Buchstaben, Groß/klein, Zahl, Symbol
    // In Production zusätzlich "uncompromised()" (HIBP-Check)
    $pwdRule = PasswordRule::min(10)
        ->letters()
        ->mixedCase()
        ->numbers()
        ->symbols();

    if (app()->isProduction()) {
        $pwdRule = $pwdRule->uncompromised();
    }

    $data = $request->validate([
        'name'                  => ['required','string','max:255'],
        'email'                 => ['required','email','max:255','unique:users,email'],
        // Passwort ist optional; wenn angegeben, muss es die Policy erfüllen und bestätigt sein
        'password'              => ['nullable','string','confirmed', $pwdRule],
        'password_confirmation' => ['nullable'],
        'roles'                 => ['sometimes','array'],
        'roles.*'               => ['string','exists:roles,name'],
        'send_reset'            => ['sometimes','boolean'],
    ]);

    $user = new User();
    $user->name  = $data['name'];
    $user->email = $data['email'];

    if (!blank($data['password'] ?? null)) {
        $user->password = Hash::make($data['password']);
    } else {
        // Fallback: Zufallspasswort, wenn keines eingegeben wurde
        $user->password = Hash::make(Str::random(16));
    }

    $user->save();

    // Rollen zuweisen
    if (!empty($data['roles'])) {
        $user->syncRoles($data['roles']);
    }

    // Reset-Link senden, wenn angefordert ODER wenn kein Passwort manuell vergeben wurde
    if ($request->boolean('send_reset') || blank($data['password'] ?? null)) {
        Password::sendResetLink(['email' => $user->email]);
    }

    return redirect()
        ->route('admin.users.index')
        ->with('success', 'Benutzer angelegt.');
}


    /* -------------------------------------------------
     | Bearbeiten
     * ------------------------------------------------*/
    public function edit(User $user)
    {
        $roles     = Role::orderBy('name')->pluck('name')->all();
        $userRoles = $user->roles->pluck('name')->all();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'                  => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', Rule::unique('users','email')->ignore($user->id)],
            'password'              => ['nullable', 'confirmed', 'min:8'],
            'roles'                 => ['array'],
            'roles.*'               => ['string'],
            'send_reset'            => ['nullable', 'boolean'],
        ]);

        // Basisdaten
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data) && $data['email'] !== null) {
            $user->email = $data['email'];
        }
        if (!empty($data['password'])) {
            $user->password = $data['password']; // hashed-cast
        }
        $user->save();

        // Rollen
        $allRoles   = Role::pluck('name')->all();
        $validRoles = array_values(array_intersect($data['roles'] ?? [], $allRoles));
        $user->syncRoles($validRoles);

        // Reset-Link auf Wunsch neu senden (z. B. wenn Email geändert)
        if ($request->boolean('send_reset')) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('success', 'Benutzer aktualisiert.');
    }

    /* -------------------------------------------------
     | Löschen (kein Selbstmord)
     * ------------------------------------------------*/
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Du kannst deinen eigenen Account nicht löschen.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Benutzer gelöscht.');
    }

    /* -------------------------------------------------
     | Reset-Link versenden (Einladung)
     * ------------------------------------------------*/
    public function sendReset(User $user)
    {
        Password::sendResetLink(['email' => $user->email]);
        return back()->with('success', 'Reset-Link gesendet an '.$user->email.'.');
    }
}
