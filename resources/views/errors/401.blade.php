<!DOCTYPE html>
<html lang="id">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div class="mb-8">
                <h2 class="mt-6 text-6xl font-extrabold text-gray-900 dark:text-gray-100">401</h2>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">Tidak Terotentikasi</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Maaf, Anda harus login terlebih dahulu untuk mengakses halaman ini.</p>
            </div>
            <div class="mt-8">
                <a href="{{ url('/login') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0V9m0 6l-3-3m3 3l3-3" />
                    </svg>
                    Pergi ke Halaman Login
                </a>
            </div>
        </div>
        <div class="mt-16 w-full max-w-2xl">
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-2 bg-gray-100 dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400">
                        Silakan login untuk melanjutkan.
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>