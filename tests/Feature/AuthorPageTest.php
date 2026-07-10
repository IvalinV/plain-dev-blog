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

    $published = Post::factory()->for($author)->create([
        'title' => 'Published Post',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('authors.show', $author->slug))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('Mathematician and writer.')
        ->assertSee('https://example.com/ada')
        ->assertSee('Published Post');
});

it('does not list unpublished posts', function () {
    $author = Author::factory()->create();

    Post::factory()->for($author)->create([
        'title' => 'Secret Draft',
        'published_at' => null,
    ]);

    $this->get(route('authors.show', $author->slug))
        ->assertOk()
        ->assertDontSee('Secret Draft');
});

it('returns 404 for an unknown author slug', function () {
    $this->get('/authors/does-not-exist')->assertNotFound();
});
