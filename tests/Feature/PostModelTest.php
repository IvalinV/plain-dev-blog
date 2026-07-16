<?php

use App\Models\Author;
use App\Models\Post;
use App\Models\Tag;

it('associates a post with an author and tags', function () {
    $author = Author::factory()->create();
    $post = Post::factory()->for($author)->create();
    $post->tags()->attach(Tag::factory()->create());

    expect($post->author->is($author))->toBeTrue()
        ->and($post->tags)->toHaveCount(1)
        ->and($author->posts)->toHaveCount(1);
});

it('auto-generates a slug from the title when blank', function () {
    $post = Post::factory()->create(['title' => 'Hello World', 'slug' => null]);

    expect($post->slug)->toBe('hello-world');
});

it('suffixes slugs to keep them unique', function () {
    Post::factory()->create(['title' => 'Duplicate Title', 'slug' => null]);
    $second = Post::factory()->create(['title' => 'Duplicate Title', 'slug' => null]);

    expect($second->slug)->toBe('duplicate-title-2');
});

it('keeps an explicitly provided slug', function () {
    $post = Post::factory()->create(['slug' => 'custom-slug']);

    expect($post->slug)->toBe('custom-slug');
});

it('auto-generates tag slugs from the name', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel Tips', 'slug' => null]);

    expect($tag->slug)->toBe('laravel-tips');
});

it('scopes to published posts and excludes drafts and scheduled posts', function () {
    $live = Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    $published = Post::published()->get();

    expect($published)->toHaveCount(1)
        ->and($published->first()->is($live))->toBeTrue();
});

it('exposes a url pointing at the blog show route', function () {
    $post = Post::factory()->create(['slug' => 'my-post']);

    expect($post->url)->toBe(route('blog.show', 'my-post'));
});

it('reports is_published for a post published exactly now', function () {
    $post = Post::factory()->create(['published_at' => now()]);

    expect($post->is_published)->toBeTrue();
});

it('reports is_published false for a scheduled post', function () {
    $post = Post::factory()->scheduled()->create();

    expect($post->is_published)->toBeFalse();
});
