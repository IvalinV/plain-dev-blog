@extends('layouts.blog')

@php
    $authorImageUrl = $author->image ? Storage::disk('public')->url($author->image) : null;
    $authorDescription = $author->bio ? Str::limit($author->bio, 155) : "Posts by {$author->name}.";
@endphp

@section('title', $author->name)
@section('meta_description', $authorDescription)

@section('content')
    <a href="{{ route('blog.index') }}" class="text-sm text-amber-600 hover:underline dark:text-amber-400">← Back to all posts</a>

    <div class="mt-8 flex flex-col items-center text-center">
        @if ($authorImageUrl)
            <img src="{{ $authorImageUrl }}" alt="{{ $author->name }}" class="h-28 w-28 rounded-full object-cover" loading="lazy" decoding="async">
        @else
            <div class="flex h-28 w-28 items-center justify-center rounded-full bg-gray-200 text-3xl font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                {{ Str::of($author->name)->substr(0, 1)->upper() }}
            </div>
        @endif

        <h1 class="mt-5 text-2xl font-bold sm:text-3xl">Hey, I'm {{ $author->name }}</h1>

        @if ($author->bio)
            <p class="mt-3 max-w-prose leading-relaxed text-gray-600 dark:text-gray-400">{{ $author->bio }}</p>
        @endif

        @if ($author->social_media)
            <a href="{{ $author->social_media }}" target="_blank" rel="me noopener noreferrer"
                class="mt-5 inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                {{ $author->social_media }} ↗
            </a>
        @endif
    </div>

    <h2 class="mt-12 text-lg font-semibold">Posts</h2>
    <div class="mt-4 space-y-6">
        @forelse ($posts as $post)
            <article class="border-b border-gray-200 pb-6 dark:border-gray-800">
                <h3 class="text-xl font-semibold">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a>
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
                </p>
                @if ($post->excerpt)
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $post->excerpt }}</p>
                @endif
            </article>
        @empty
            <p class="text-gray-500 dark:text-gray-400">No published posts yet.</p>
        @endforelse
    </div>
@endsection
