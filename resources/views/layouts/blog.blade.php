<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $siteName = 'Plain Dev Blog';
        $pageTitle = trim($__env->yieldContent('title')) ?: $siteName;
        $metaDescription = trim($__env->yieldContent('meta_description'))
            ?: 'Plain Dev Blog — articles and tutorials on software development.';
        $ogImage = trim($__env->yieldContent('og_image'));
    @endphp

    <title>{{ $pageTitle === $siteName ? $siteName : "{$pageTitle} · {$siteName}" }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @stack('structured-data')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-6">
            <a href="{{ route('blog.index') }}" class="text-xl font-semibold">Plain Dev Blog</a>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-10">
        @yield('content')
    </main>
</body>
</html>
