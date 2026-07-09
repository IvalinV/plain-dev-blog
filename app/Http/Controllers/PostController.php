<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $tag = null;

        $posts = Post::published()
            ->with(['author', 'tags'])
            ->when($request->query('tag'), function ($query, string $tagSlug) use (&$tag): void {
                $tag = Tag::where('slug', $tagSlug)->firstOrFail();
                $query->whereHas('tags', fn ($relation) => $relation->whereKey($tag->getKey()));
            })
            ->latest('published_at')
            ->paginate(10);

        return view('blog.index', ['posts' => $posts, 'tag' => $tag]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published, 404);

        $post->load(['author', 'tags']);

        return view('blog.show', ['post' => $post]);
    }
}
