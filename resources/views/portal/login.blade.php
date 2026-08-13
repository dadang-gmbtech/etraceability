<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petani — e-Traceability Gula Kelapa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        {{-- Logo / Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500 shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="w-9 h-9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Portal Petani</h1>
            <p class="text-gray-500 text-sm mt-1">e-Traceability Gula Kelapa</p>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-5">
                @csrf

                {{-- Error global --}}
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-2.5 rounded-lg border @error('email') border-red-400 @else border-gray-300 @enderror text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm"
                        placeholder="email@contoh.com"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm"
                        placeholder="••••••••"
                    >
                </div>

                {{-- Remember --}}
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 shadow-sm text-sm"
                >
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Hubungi admin jika belum memiliki akun atau lupa password.
        </p>

    </div>

</body>
</html>
