<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>








    <x-slot name="header">
        <h2 class="font-semibold text-xl">Profil</h2>
    </x-slot>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4 max-w-lg">
        @csrf
        @method('patch')
        <label class="block">
            <span>Name</span>
            <input class="form-input" name="name" value="{{ old('name', $user->name) }}">
        </label>
        <label class="block">
            <span>E-Mail</span>
            <input class="form-input" name="email" value="{{ old('email', $user->email) }}">
        </label>
        <button class="btn btn-primary">Speichern</button>
    </form>
</x-app-layout>

