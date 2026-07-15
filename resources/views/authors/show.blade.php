@extends('layouts.blog')

@php
    $authorImageUrl = $author->image ? Storage::disk('s3')->url($author->image) : null;
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

            <div class="mx-auto max-w-7xl overflow-hidden px-6 lg:px-8">
                <div class="mt-16 flex justify-center gap-x-10">
                    <a href="https://x.com/IvallinV" target="_blank" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                        <span class="sr-only">X</span>
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
                            <path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z" />
                        </svg>
                    </a>
                    <a href="https://github.com/IvalinV" target="_blank" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                        <span class="sr-only">GitHub</span>
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
                            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" fill-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="{{ $author->social_media }}" target="_blank" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                        <span class="sr-only">LinkedIn</span>
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
                            <path d="M20.447 20.452H16.89v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.346V9h3.414v1.561h.049c.476-.9 1.637-1.85 3.37-1.85 3.604 0 4.268 2.372 4.268 5.456v6.285ZM5.337 7.433a2.063 2.063 0 1 1 0-4.126 2.063 2.063 0 0 1 0 4.126ZM7.119 20.452H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003Z"/>
                        </svg>
                    </a>
                </div>
            </div>
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
