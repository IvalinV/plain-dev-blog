<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

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

        if ($tag) {
            Head::title("Posts tagged $tag->name");
            Head::description("Articles and tutorials tagged $tag->name on Plain Dev Blog.");
        }

        Head::canonical(url()->current(), forceHttps: app()->isProduction());
        Head::og(url: url()->current());

        return view('blog.index', ['posts' => $posts, 'tag' => $tag]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published, 404);

        $post->load(['author', 'tags']);

        $postDescription = $post->excerpt ?: Str::limit(strip_tags($post->body), 155);
        $postImageUrl = $post->image ? Storage::disk('s3')->url($post->image) : null;

        Head::title($post->title);
        Head::description($postDescription);
        Head::canonical(route('blog.show', $post->slug), forceHttps: app()->isProduction());
        Head::og(type: OgType::Article, url: route('blog.show', $post->slug));

        if ($postImageUrl !== null) {
            Head::ogImage($postImageUrl);
            Head::twitterImage($postImageUrl);
        }

        return view('blog.show', ['post' => $post]);
    }
}
