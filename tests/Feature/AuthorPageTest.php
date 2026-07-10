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
