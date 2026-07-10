<?php

use App\Models\Author;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows author info, social link, and published posts', function () {
    $author = Author::factory()->create([
        'name' => 'Ada Lovelace',
        'bio' => 'Mathematician and writer.',
        'social_media' => 'https://example.com/ada',
    ]);

    $published = Post::factory()->for($author)->published()->create([
        'title' => 'Published Post',
    ]);

    $this->get(route('authors.show', $author->slug))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('Mathematician and writer.')
        ->assertSee($published->title)
        ->assertSee('https://example.com/ada')
        // confirm the social pill actually renders with new-tab behavior,
        // not just that the URL appears somewhere in the HTML
        ->assertSee('target="_blank"', escape: false)
        ->assertSee('rel="me noopener noreferrer"', escape: false);
});

it('does not list unpublished posts', function () {
    $author = Author::factory()->create();

    Post::factory()->for($author)->draft()->create([
        'title' => 'Secret Draft',
    ]);

    $this->get(route('authors.show', $author->slug))
        ->assertOk()
        ->assertDontSee('Secret Draft');
});

it('returns 404 for an unknown author slug', function () {
    $this->get('/authors/does-not-exist')->assertNotFound();
});

it('renders an initials fallback when the author has no image', function () {
    $author = Author::factory()->create(['name' => 'Zoe Example', 'image' => null]);

    $response = $this->get(route('authors.show', $author->slug))
        ->assertOk()
        // no image means no <img> tag is rendered
        ->assertDontSee('<img', escape: false);

    // the uppercased first initial should render as its own text node,
    // regardless of the CSS classes wrapping it. Whitespace between tags
    // is normalized first since Blade output includes surrounding
    // newlines/indentation around the interpolated initial.
    $normalizedHtml = preg_replace('/>\s+/', '>', $response->getContent());
    $normalizedHtml = preg_replace('/\s+</', '<', $normalizedHtml);

    expect($normalizedHtml)->toContain('>Z<');
});
