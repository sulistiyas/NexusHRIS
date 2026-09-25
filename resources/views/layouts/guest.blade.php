<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NexusHRIS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-900 via-[#0B1E36] to-sky-950 text-slate-100 antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">
    
    <div class="w-full max-w-md">
        @yield('content')

        <!-- Footer Copyright -->
        <p class="text-center text-xs text-sky-200/60 mt-6 font-medium">
            &copy; {{ date('Y') }} NexusHRIS. Sistem Keamanan Terpadu.
        </p>
    </div>

</body>
</html>
