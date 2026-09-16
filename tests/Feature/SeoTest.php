<?php

use App\Models\Author;
use App\Models\Post;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->withoutVite();
    config(['app.url' => 'https://ivalin.dev']);
    URL::forceRootUrl('https://ivalin.dev');
    URL::forceScheme('https');
});

it('uses the production domain in default metadata', function () {
    $this->get(route('blog.index'))
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="https://ivalin.dev/">', escape: false)
        ->assertSee('<meta property="og:url" content="https://ivalin.dev">', escape: false)
        ->assertSee('<meta property="og:site_name" content="Plain Dev Blog">', escape: false)
        ->assertSee('"@type":"Organization"', escape: false)
        ->assertSee('"@type":"WebSite"', escape: false);
});

it('preserves the local host and scheme outside production', function () {
    config([
        'app.env' => 'local',
        'app.url' => 'http://127.0.0.1:8000',
    ]);
    URL::forceRootUrl('http://127.0.0.1:8000');
    URL::forceScheme('http');

    $this->get(route('blog.index'))
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="http://127.0.0.1:8000/">', escape: false);

    $this->get('/llms.txt')
        ->assertSuccessful()
        ->assertSee('http://127.0.0.1:8000/')
        ->assertSee('http://127.0.0.1:8000/sitemap.xml');
});

it('renders article and author structured data', function () {
    $author = Author::factory()->create([
        'name' => 'Ada Lovelace',
        'bio' => 'Mathematician and writer.',
    ]);
    $post = Post::factory()->for($author)->published()->create([
        'title' => 'A Searchable Post',
        'excerpt' => 'A concise description for search engines.',
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="https://ivalin.dev/blog/'.$post->slug.'">', escape: false)
        ->assertSee('<meta property="og:type" content="article">', escape: false)
        ->assertSee('"@type":"BlogPosting"', escape: false)
        ->assertSee('"headline":"A Searchable Post"', escape: false)
        ->assertSee('"@type":"Person"', escape: false)
        ->assertSee('"name":"Ada Lovelace"', escape: false);

    $this->get(route('authors.show', $author->slug))
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="https://ivalin.dev/authors/'.$author->slug.'">', escape: false)
        ->assertSee('<meta property="og:type" content="profile">', escape: false)
        ->assertSee('"@type":"Person"', escape: false)
        ->assertSee('"name":"Ada Lovelace"', escape: false);
});

it('includes published posts and authors in the sitemap', function () {
    $author = Author::factory()->create();
    $post = Post::factory()->for($author)->published()->create();

    $this->get(route('sitemap'))
        ->assertSuccessful()
        ->assertSee(route('blog.show', $post->slug), escape: false)
        ->assertSee(route('authors.show', $author->slug), escape: false)
        ->assertSee('https://ivalin.dev', escape: false);
});

it('allows search and AI crawlers and advertises the sitemap', function () {
    $this->get('/robots.txt')
        ->assertSuccessful()
        ->assertSee('GPTBot')
        ->assertSee('ChatGPT-User')
        ->assertSee('PerplexityBot')
        ->assertSee('ClaudeBot')
        ->assertSee('Google-Extended')
        ->assertSee('Sitemap: https://ivalin.dev/sitemap.xml');
});

it('provides an llms context file', function () {
    $this->get('/llms.txt')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('# Plain Dev Blog')
        ->assertSee('https://ivalin.dev/');
});
