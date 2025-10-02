@php
  // Fallbacks, falls nicht gesetzt
  /** @var \App\Models\User|null $user */
  $user = $user ?? null;
  $userRoles = $userRoles ?? [];

  // Rollen-Quelle vereinheitlichen (Array von Strings)
  if ($roles instanceof \Illuminate\Support\Collection) {
      $roles = $roles->pluck('name')->all();
  } elseif (is_array($roles)) {
      $roles = array_values($roles);
  } else {
      $roles = [];
  }
@endphp

@csrf

<div class="grid md:grid-cols-2 gap-4">
  {{-- Name --}}
  <div>
    <label class="block text-sm mb-1">Name *</label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $user->name ?? '') }}"
      required
      autocomplete="name"
      class="border rounded w-full px-3 py-2 @error('name') border-red-500 @enderror"
    >
    @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- E-Mail --}}
  <div>
    <label class="block text-sm mb-1">E-Mail *</label>
    <input
      type="email"
      name="email"
      value="{{ old('email', $user->email ?? '') }}"
      required
      autocomplete="email"
      class="border rounded w-full px-3 py-2 @error('email') border-red-500 @enderror"
    >
    @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Passwort (optional) --}}
  <div>
    <label class="block text-sm mb-1">Passwort (optional)</label>
    <input
      type="password"
      name="password"
      autocomplete="new-password"
      class="border rounded w-full px-3 py-2 @error('password') border-red-500 @enderror"
    >
    <p class="text-xs text-gray-500 mt-1">
      Leer lassen, wenn du kein Passwort setzen/ändern möchtest.
    </p>
    @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Passwort bestätigen --}}
  <div>
    <label class="block text-sm mb-1">Passwort bestätigen</label>
    <input
      type="password"
      name="password_confirmation"
      autocomplete="new-password"
      class="border rounded w-full px-3 py-2"
    >
  </div>

  {{-- Rollen --}}
  <div class="md:col-span-2">
    <label class="block text-sm mb-2">Rollen</label>

    @if (empty($roles))
      <p class="text-sm text-gray-500">Keine Rollen definiert.</p>
    @else
      <div class="flex flex-wrap gap-3">
        @foreach($roles as $r)
          <label class="inline-flex items-center gap-2">
            <input
              type="checkbox"
              name="roles[]"
              value="{{ $r }}"
              @checked(in_array($r, old('roles', $userRoles), true))
            >
            <span>{{ $r }}</span>
          </label>
        @endforeach
      </div>
    @endif

    @error('roles')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    @error('roles.*')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Reset-Link senden --}}
  <div class="md:col-span-2">
    <label class="inline-flex items-center gap-2">
      <input
        type="checkbox"
        name="send_reset"
        value="1"
        @checked(old('send_reset', !($user && $user->exists)))
      >
      <span>Passwort-Reset-Link per E-Mail senden</span>
    </label>
    <p class="text-xs text-gray-500 mt-1">
      Praktisch für Einladungen. Wenn kein Passwort eingegeben wird, bekommt der Benutzer einen Reset-Link (sofern Mail konfiguriert ist).
    </p>
  </div>
</div>

<div class="mt-6 flex items-center gap-3">
  <button class="px-4 py-2 bg-blue-600 text-white rounded">
    Speichern
  </button>
  <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
</div>
