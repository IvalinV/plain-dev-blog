@extends('layouts.blog')

@php
    $postImageUrl = $post->image ? Storage::disk('s3')->url($post->image) : null;
    $postDescription = $post->excerpt ?: Str::limit(strip_tags($post->body), 155);
@endphp

@section('title', $post->title)
@section('meta_description', $postDescription)
@section('og_type', 'article')
@section('og_image', $postImageUrl)

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $postDescription,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => $post->author->social_media ?: null,
            ],
            'image' => $postImageUrl,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post->slug),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <article>
        <a href="{{ route('blog.index') }}" class="text-sm text-amber-600 hover:underline dark:text-amber-400">← Back to all posts</a>

        <h1 class="mt-4 text-2xl font-bold sm:text-3xl">{{ $post->title }}</h1>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('authors.show', $post->author->slug) }}" class="hover:underline" rel="author">{{ $post->author->name }}</a>
            · <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
        </p>

        @if ($post->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($post->tags as $postTag)
                    <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">#{{ $postTag->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($postImageUrl)
            <img src="{{ $postImageUrl }}" alt="{{ $post->title }}" class="mt-6 w-full rounded" loading="lazy" decoding="async">
        @endif

        <div class="mt-6 space-y-4 wrap-break-words leading-relaxed
            [&_a]:text-amber-600 [&_a]:underline dark:[&_a]:text-amber-400
            [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-4 [&_blockquote]:text-gray-600 dark:[&_blockquote]:border-gray-700 dark:[&_blockquote]:text-gray-400
            [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold sm:[&_h2]:text-2xl
            [&_h3]:mt-6 [&_h3]:text-lg [&_h3]:font-semibold sm:[&_h3]:text-xl
            [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded
            [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6
            [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-gray-900 [&_pre]:p-4 [&_pre]:text-sm [&_pre]:text-gray-100
            [&_table]:block [&_table]:w-full [&_table]:overflow-x-auto">
            {!! $post->body !!}
        </div>

        <div class="mt-10 border-t border-gray-200 pt-8 dark:border-gray-800">
            <p class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Share this post</p>
            {!! ShareButtons::currentPage($post->title, [
                    'rel' => 'nofollow noopener noreferrer',
                ])
                ->twitter("/blog/$post->slug")
                ->linkedin("/blog/$post->slug")
                ->reddit("/blog/$post->slug")
                ->copylink()
                ->render()
            !!}
        </div>
    </article>
@endsection
