<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dev Blog')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-6">
            <a href="{{ route('blog.index') }}" class="text-xl font-semibold">Dev Blog</a>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-10">
        @yield('content')
    </main>
</body>
</html>
