<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Head\Facades\Head;

class AuthorController extends Controller
{
    public function show(Author $author): View
    {
        $authorImageUrl = $author->image ? Storage::disk('s3')->url($author->image) : null;
        $authorDescription = $author->bio ? Str::limit($author->bio, 155) : "Posts by {$author->name}.";

        Head::title($author->name);
        Head::description($authorDescription);

        if ($authorImageUrl !== null) {
            Head::ogImage($authorImageUrl);
        }

        $posts = $author->posts()
            ->published()
            ->latest('published_at')
            ->get();

        return view('authors.show', ['author' => $author, 'posts' => $posts]);
    }
}
