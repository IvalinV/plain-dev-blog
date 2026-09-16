@extends('layouts.blog')

@php
    $postImageUrl = $post->image ? Storage::disk('s3')->url($post->image) : null;
@endphp

@push('structured-data')
    <script type="application/ld+json">
        {!!
            json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->excerpt ?: Str::limit(strip_tags($post->body), 155),
                'url' => route('blog.show', $post->slug),
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
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Plain Dev Blog',
                    'url' => url('/'),
                ],
                'keywords' => $post->tags->pluck('name')->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        !!}
    </script>
@endpush

@section('content')
    <article>
        <a href="{{ route('blog.index') }}" class="text-sm text-amber-600 hover:underline dark:text-amber-400"
            >← Back to all posts</a>

        <h1 class="mt-4 text-2xl font-bold sm:text-3xl">{{ $post->title }}</h1>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            <a
                href="{{ route('authors.show', $post->author->slug) }}"
                class="hover:underline"
                rel="author"
            >{{ $post->author->name }}</a>
            ·
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
        </p>

        @if ($post->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($post->tags as $postTag)
                    <a
                        href="{{ route('blog.index', ['tag' => $postTag->slug]) }}"
                        class="rounded bg-gray-100 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >#{{ $postTag->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($postImageUrl)
            <img
                src="{{ $postImageUrl }}"
                alt="{{ $post->title }}"
                width="1600"
                height="900"
                class="mt-6 w-full rounded"
                loading="lazy"
                decoding="async"
            />
        @endif

        <div class="wrap-break-words [&_a]:text-amber-600 [&_a]:underline dark:[&_a]:text-amber-400 [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-4 [&_blockquote]:text-gray-600 dark:[&_blockquote]:border-gray-700 dark:[&_blockquote]:text-gray-400 [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold sm:[&_h2]:text-2xl [&_h3]:mt-6 [&_h3]:text-lg [&_h3]:font-semibold sm:[&_h3]:text-xl [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-gray-900 [&_pre]:p-4 [&_pre]:text-sm [&_pre]:text-gray-100 [&_table]:block [&_table]:w-full [&_table]:border-collapse [&_table]:overflow-x-auto [&_td]:border [&_td]:border-gray-300 [&_td]:p-2 [&_td]:align-top dark:[&_td]:border-gray-600 [&_th]:border [&_th]:border-gray-300 [&_th]:bg-gray-100 [&_th]:p-2 [&_th]:text-start [&_th]:align-top [&_th]:font-bold dark:[&_th]:border-gray-600 dark:[&_th]:bg-gray-800 dark:[&_th]:text-white mt-6 space-y-4 leading-relaxed">
            {!! $post->body !!}
        </div>

        <div class="mt-10 border-t border-gray-200 pt-4 pb-10 dark:border-gray-800">
            <p class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Share this post</p>
            {!!
                ShareButtons::currentPage($post->title, [
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
