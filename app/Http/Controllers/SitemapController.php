<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->with('author:id,slug,updated_at')
            ->latest('published_at')
            ->get(['slug', 'updated_at', 'author_id']);
        $authors = $posts
            ->pluck('author')
            ->filter()
            ->unique('id')
            ->sortByDesc('updated_at')
            ->values();

        return response()
            ->view('sitemap', ['posts' => $posts, 'authors' => $authors])
            ->header('Content-Type', 'application/xml');
    }
}
