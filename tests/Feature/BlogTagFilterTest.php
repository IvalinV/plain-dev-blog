<?php

use App\Models\Post;
use App\Models\Tag;

beforeEach(function () {
    $this->withoutVite();
});

it('filters the index to a single tag', function () {
    $laravel = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $php = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);

    $matching = Post::factory()->published()->create(['title' => 'Matching Post']);
    $matching->tags()->attach($laravel);

    $other = Post::factory()->published()->create(['title' => 'Other Post']);
    $other->tags()->attach($php);

    $this->get(route('blog.index', ['tag' => 'laravel']))
        ->assertOk()
        ->assertSee('Matching Post')
        ->assertDontSee('Other Post');
});

it('404s for an unknown tag slug', function () {
    $this->get(route('blog.index', ['tag' => 'does-not-exist']))
        ->assertNotFound();
});
