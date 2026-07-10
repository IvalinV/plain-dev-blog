<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('renders a published post', function () {
    $post = Post::factory()->published()->create([
        'title' => 'A Published Post',
        'body' => '<p>Body content here.</p>',
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('A Published Post')
        ->assertSee('Body content here.', escape: false);
});

it('404s for a draft post', function () {
    $post = Post::factory()->draft()->create();

    $this->get(route('blog.show', $post->slug))->assertNotFound();
});

it('404s for a scheduled post', function () {
    $post = Post::factory()->scheduled()->create();

    $this->get(route('blog.show', $post->slug))->assertNotFound();
});

it('404s for an unknown slug', function () {
    $this->get(route('blog.show', 'no-such-slug'))->assertNotFound();
});
