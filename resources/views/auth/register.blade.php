<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-lg font-semibold text-gray-700">Daftar Akun e-Traceability</h2>
        <p class="text-sm text-gray-500 mt-1">Pendaftaran memerlukan persetujuan admin sebelum bisa digunakan.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nama -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" value="Daftar Sebagai" />
            <select id="role" name="role" required
                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Pilih Role --</option>
                <option value="petani"   {{ old('role') === 'petani'   ? 'selected' : '' }}>Petani</option>
                <option value="pengepul" {{ old('role') === 'pengepul' ? 'selected' : '' }}>Pengepul</option>
                <option value="kub"      {{ old('role') === 'kub'      ? 'selected' : '' }}>KUB</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-gray-600 hover:text-gray-900 underline" href="{{ route('filament.admin.auth.login') }}">
                Sudah punya akun?
            </a>
            <x-primary-button>Daftar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
