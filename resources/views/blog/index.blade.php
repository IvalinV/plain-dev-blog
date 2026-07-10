@extends('layouts.blog')

@section('title', $tag ? "Posts tagged $tag->name" : 'Plain Dev Blog')
@section('meta_description', $tag
    ? "Articles and tutorials tagged {$tag->name} on Plain Dev Blog."
    : 'Plain Dev Blog — articles and tutorials on software development.')

@section('content')
    @if ($tag)
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Showing posts tagged <span class="font-medium">{{ $tag->name }}</span> ·
            <a href="{{ route('blog.index') }}" class="text-amber-600 hover:underline dark:text-amber-400">clear</a>
        </p>
    @endif

    @forelse ($posts as $post)
        <article class="mb-8 border-b border-gray-200 pb-8 dark:border-gray-800">
            @if ($post->image)
                <a href="{{ route('blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
                    <img src="{{ Storage::disk('public')->url($post->image) }}" alt="{{ $post->title }}" width="1600" height="900" class="mb-4 aspect-video w-full rounded object-cover" loading="lazy" decoding="async">
                </a>
            @endif
            <h2 class="text-xl font-semibold sm:text-2xl">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-amber-600 dark:hover:text-amber-400">{{ $post->title }}</a>
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $post->author->name }} · <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
            </p>
            @if ($post->excerpt)
                <p class="mt-3 text-gray-700 dark:text-gray-300">{{ $post->excerpt }}</p>
            @endif
            @if ($post->tags->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($post->tags as $postTag)
                        <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">#{{ $postTag->name }}</a>
                    @endforeach
                </div>
            @endif
        </article>
    @empty
        <p class="text-gray-500 dark:text-gray-400">No posts yet.</p>
    @endforelse

    <div class="mt-8">
        {{ $posts->withQueryString()->links() }}
    </div>
@endsection
