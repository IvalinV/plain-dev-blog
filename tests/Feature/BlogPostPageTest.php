<?php

use App\Models\Author;
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

it('renders social share buttons', function () {
    $post = Post::factory()->published()->create();

    $response = $this->get(route('blog.show', $post->slug))->assertOk();

    $response->assertSee('social-buttons', escape: false);
    $response->assertSee('twitter.com/intent/tweet', escape: false);
    $response->assertSee(urlencode(route('blog.show', $post->slug)), escape: false);

    // Buttons render as inline SVGs, not icon-font spans.
    $response->assertSee('<svg', escape: false);
    $response->assertSee('linkedin.com/sharing/share-offsite', escape: false);
    $response->assertSee('reddit.com/submit', escape: false);
    $response->assertSee('id="clip"', escape: false);

    // Click handling depends on every share-button svg carrying this class.
    $response->assertSee('pointer-events-none', escape: false);

    // Font Awesome CDN is gone; icons are self-hosted SVGs.
    $response->assertDontSee('font-awesome', escape: false);
    $response->assertDontSee('class="fab', escape: false);
    $response->assertDontSee('class="fas', escape: false);
});

it('styles tables in the post body', function () {
    $post = Post::factory()->published()->create([
        'body' => '<table style="min-width: 50px"><colgroup><col><col></colgroup><tbody>'
            .'<tr><th><p>Condition</p></th><th><p>Resolution</p></th></tr>'
            .'<tr><td><p>Status trialing</p></td><td><p>End the trial.</p></td></tr>'
            .'</tbody></table>',
    ]);

    $response = $this->get(route('blog.show', $post->slug))->assertOk();

    // The editor's table markup must survive untouched.
    $response->assertSee('<th><p>Condition</p></th>', escape: false);

    // The frontend has no Tailwind Typography or fi-prose, so the post body
    // styles are the only thing giving rich editor tables their grid.
    $response->assertSee('[&_table]:border-collapse', escape: false);
    $response->assertSee('[&_th]:border', escape: false);
    $response->assertSee('[&_td]:border', escape: false);
    $response->assertSee('[&_th]:p-2', escape: false);
    $response->assertSee('[&_td]:p-2', escape: false);
    $response->assertSee('[&_th]:text-start', escape: false);
    $response->assertSee('[&_th]:font-bold', escape: false);
});

it('links the author name to the author page', function () {
    $author = Author::factory()->create(['name' => 'Ada Lovelace']);
    $post = Post::factory()->for($author)->published()->create();

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee(route('authors.show', $author->slug), escape: false)
        ->assertSee('Ada Lovelace');
});
