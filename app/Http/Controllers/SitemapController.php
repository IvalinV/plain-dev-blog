<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->latest('published_at')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', ['posts' => $posts])
            ->header('Content-Type', 'application/xml');
    }
}
