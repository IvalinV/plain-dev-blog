@extends('layouts.blog')

@section('title', $tag ? "Posts tagged {$tag->name}" : 'Dev Blog')

@section('content')
    @if ($tag)
        <p class="mb-6 text-sm text-gray-500">
            Showing posts tagged <span class="font-medium">{{ $tag->name }}</span> ·
            <a href="{{ route('blog.index') }}" class="text-amber-600 hover:underline">clear</a>
        </p>
    @endif

    @forelse ($posts as $post)
        <article class="mb-8 border-b border-gray-200 pb-8">
            @if ($post->image)
                <img src="{{ Storage::disk('public')->url($post->image) }}" alt="" class="mb-4 aspect-video w-full rounded object-cover">
            @endif
            <h2 class="text-2xl font-semibold">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-amber-600">{{ $post->title }}</a>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $post->author->name }} · {{ $post->published_at->format('M j, Y') }}
            </p>
            @if ($post->excerpt)
                <p class="mt-3 text-gray-700">{{ $post->excerpt }}</p>
            @endif
            @if ($post->tags->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($post->tags as $postTag)
                        <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">#{{ $postTag->name }}</a>
                    @endforeach
                </div>
            @endif
        </article>
    @empty
        <p class="text-gray-500">No posts yet.</p>
    @endforelse

    <div class="mt-8">
        {{ $posts->withQueryString()->links() }}
    </div>
@endsection
