@if(session('google_error'))
<div class="mb-4 p-3 rounded-lg bg-danger-50 dark:bg-danger-950 border border-danger-200 dark:border-danger-800 text-sm text-danger-600 dark:text-danger-400">
    {{ session('google_error') }}
</div>
@endif

<div class="mt-2">
    <div class="relative flex items-center py-3">
        <div class="flex-grow border-t border-gray-200 dark:border-white/10"></div>
        <span class="flex-shrink-0 mx-4 text-xs text-gray-400 dark:text-gray-500">atau</span>
        <div class="flex-grow border-t border-gray-200 dark:border-white/10"></div>
    </div>

    <a href="{{ route('google.login') }}"
       class="w-full flex items-center justify-center px-4 py-2.5 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/10 transition-colors duration-150"
    >
        Masuk dengan Google
    </a>
</div>
