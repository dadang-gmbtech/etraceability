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
       style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:10px 16px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:14px;font-weight:500;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.05);transition:background 0.15s;"
       onmouseover="this.style.background='#f9fafb'"
       onmouseout="this.style.background='#fff'"
    >
        <svg width="20" height="20" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
            <path d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
        </svg>
        Masuk dengan Google
    </a>
</div>
