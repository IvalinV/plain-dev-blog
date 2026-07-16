<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Author;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the posts list page', function () {
    Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)->assertOk();
});

it('creates a post with an author and tags', function () {
    $author = Author::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'My First Post',
            'author_id' => $author->id,
            'body' => '<p>Hello.</p>',
            'tags' => [$tag->id],
            'published_at' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Post::class, [
        'title' => 'My First Post',
        'slug' => 'my-first-post',
        'author_id' => $author->id,
    ]);

    expect(Post::firstWhere('title', 'My First Post')->tags)->toHaveCount(1);
});

it('requires a title and a body', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => null,
            'body' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title', 'body']);
});

it('shows the read-only url on the edit page', function () {
    $post = Post::factory()->create(['slug' => 'my-post']);

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->assertFormFieldExists('url')
        ->assertFormSet(['url' => $post->url]);
});

it('copies the url to the clipboard from the edit page', function () {
    $post = Post::factory()->create(['slug' => 'my-post']);

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->callFormComponentAction('url', 'copyUrl')
        ->assertNotified('URL copied to clipboard');
});

it('loads the edit page and updates a post', function () {
    $post = Post::factory()->create();

    Livewire::test(EditPost::class, ['record' => $post->getKey()])
        ->assertOk()
        ->fillForm(['title' => 'Updated Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Post::class, [
        'id' => $post->id,
        'title' => 'Updated Title',
    ]);
});
