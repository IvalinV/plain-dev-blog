@extends('layouts.blog')

@section('title', $post->title)

@section('content')
    <article>
        <a href="{{ route('blog.index') }}" class="text-sm text-amber-600 hover:underline">← Back to all posts</a>

        <h1 class="mt-4 text-3xl font-bold">{{ $post->title }}</h1>

        <p class="mt-2 text-sm text-gray-500">
            @if ($post->author->social_media)
                <a href="{{ $post->author->social_media }}" class="hover:underline">{{ $post->author->name }}</a>
            @else
                {{ $post->author->name }}
            @endif
            · {{ $post->published_at->format('M j, Y') }}
        </p>

        @if ($post->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($post->tags as $postTag)
                    <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">#{{ $postTag->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($post->image)
            <img src="{{ Storage::disk('public')->url($post->image) }}" alt="" class="mt-6 w-full rounded">
        @endif

        <div class="mt-6 space-y-4 leading-relaxed">
            {!! $post->body !!}
        </div>
    </article>
@endsection
