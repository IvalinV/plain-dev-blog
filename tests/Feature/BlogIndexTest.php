<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('shows published posts on the index', function () {
    Post::factory()->published()->create(['title' => 'Live Post']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('Live Post');
});

it('hides drafts and scheduled posts from the index', function () {
    Post::factory()->draft()->create(['title' => 'Draft Post']);
    Post::factory()->scheduled()->create(['title' => 'Scheduled Post']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertDontSee('Draft Post')
        ->assertDontSee('Scheduled Post');
});

it('orders posts newest first', function () {
    Post::factory()->create(['title' => 'Older', 'published_at' => now()->subWeek()]);
    Post::factory()->create(['title' => 'Newer', 'published_at' => now()->subDay()]);

    $response = $this->get(route('blog.index'))->assertOk();

    expect(strpos($response->getContent(), 'Newer'))
        ->toBeLessThan(strpos($response->getContent(), 'Older'));
});
