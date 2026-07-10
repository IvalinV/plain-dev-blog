<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    public function show(Author $author): View
    {
        $posts = $author->posts()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->get();

        return view('authors.show', ['author' => $author, 'posts' => $posts]);
    }
}
