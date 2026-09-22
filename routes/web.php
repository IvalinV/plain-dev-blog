<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/authors/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::view('/apps/codepad/privacy', 'apps.codepad.privacy')->name('apps.codepad.privacy');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nDisallow:\n\n"
        .'User-agent: GPTBot'."\nAllow: /\n\n"
        .'User-agent: ChatGPT-User'."\nAllow: /\n\n"
        .'User-agent: PerplexityBot'."\nAllow: /\n\n"
        .'User-agent: ClaudeBot'."\nAllow: /\n\n"
        .'User-agent: anthropic-ai'."\nAllow: /\n\n"
        .'User-agent: Google-Extended'."\nAllow: /\n\n"
        .'Sitemap: '.url('/sitemap.xml')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
});

Route::get('/llms.txt', function () {
    $siteUrl = rtrim(url('/'), '/');
    $content = <<<TXT
# Plain Dev Blog

Plain Dev Blog is a developer blog by Ivalin Venkov covering software development, programming, and practical engineering tutorials.

## Important URLs
- Homepage: {$siteUrl}/
- Sitemap: {$siteUrl}/sitemap.xml
- Robots: {$siteUrl}/robots.txt
TXT;

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});
