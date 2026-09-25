<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NexusHRIS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex">

    <!-- 1. Sidebar Navigasi -->
    @include('layouts.partials.sidebar')

    <!-- 2. Area Konten Utama -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        
        <!-- Header Topbar -->
        @include('layouts.partials.header')

        <!-- Dynamic Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('layouts.partials.footer')

    </div>

</body>
</html>
