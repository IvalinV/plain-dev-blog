# Filament Dev Blog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a native Laravel + Filament dev blog — posts with tags, draft/published state, a Filament authoring UI, and a minimal public site (index + single post).

**Architecture:** Domain layer (`Author`, `Post`, `Tag` models over four migrations) with slug auto-generation via a reusable `HasUniqueSlug` trait and a `published()` query scope keyed on `published_at`. A thin `PostController` renders Blade+Tailwind public pages. Three auto-discovered Filament v5 resources provide admin CRUD. No third-party slug package (that is what blocked the original `binshops/laravel-blog` install on Laravel 13).

**Tech Stack:** Laravel 13.19, Filament v5.6, Livewire v4, PHP 8.4, Pest v4, Tailwind CSS v4, SQLite (`:memory:`) for tests.

## Global Constraints

- PHP `^8.3`; code targets PHP 8.4 features are fine but not required.
- Laravel `^13.8`, Filament `^5.0` — no dependency changes without approval.
- **No slug package** — slugs generated with `Illuminate\Support\Str::slug` + in-app uniqueness.
- Draft/published state is modeled by `published_at` (nullable timestamp). Null = draft; a value `<= now()` = live; a future value = scheduled (not shown publicly). There is **no** `published` boolean.
- Authorship is via the separate `authors` table (`author_id`), not `users`.
- Post `body` is sanitized HTML from Filament's `RichEditor`; render with `{!! !!}`. Authors are trusted admins only.
- Curly braces on all control structures; explicit return types and param type hints; constructor property promotion where a constructor is needed.
- Run `vendor/bin/pint --dirty --format agent` before each commit.
- Public single-post route binds by slug: `/blog/{post:slug}`. Generate its URL with `route('blog.show', $post->slug)`.
- Filament resources are auto-discovered by the existing `AdminPanelProvider` — do not edit the panel provider.

---

### Task 1: Domain layer — migrations, models, slug trait, factories

**Files:**
- Modify: `database/migrations/2026_07_09_145404_create_authors_table.php`
- Modify: `database/migrations/2026_07_09_145418_create_posts_table.php`
- Create: `database/migrations/2026_07_09_145430_create_tags_table.php`
- Create: `database/migrations/2026_07_09_145440_create_post_tag_table.php`
- Create: `app/Models/Concerns/HasUniqueSlug.php`
- Create: `app/Models/Author.php`
- Create: `app/Models/Tag.php`
- Create: `app/Models/Post.php`
- Create: `database/factories/AuthorFactory.php`
- Create: `database/factories/TagFactory.php`
- Create: `database/factories/PostFactory.php`
- Modify: `tests/Pest.php` (enable `RefreshDatabase` for `Feature`)
- Test: `tests/Feature/PostModelTest.php`

**Interfaces:**
- Consumes: nothing (first task).
- Produces:
  - `App\Models\Author` — `hasMany` `posts`; fillable `name`, `email`, `social_media`.
  - `App\Models\Tag` — `belongsToMany` `posts`; fillable `name`, `slug`; auto-slug from `name`.
  - `App\Models\Post` — `belongsTo` `author`, `belongsToMany` `tags`; fillable `author_id`, `title`, `slug`, `excerpt`, `body`, `image`, `published_at`; cast `published_at` → `datetime`; `scopePublished(Builder $q): void`; `is_published` boolean accessor; auto-slug from `title`.
  - Factories: `AuthorFactory`, `TagFactory`, `PostFactory` with `published()`, `draft()`, `scheduled()` states.

- [ ] **Step 1: Enable RefreshDatabase for Feature tests**

Edit `tests/Pest.php` — ensure the `Feature` binding uses `RefreshDatabase`:

```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```

- [ ] **Step 2: Write the failing domain test**

Create `tests/Feature/PostModelTest.php`:

```php
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

it('reports is_published for a post published exactly now', function () {
    $post = Post::factory()->create(['published_at' => now()]);

    expect($post->is_published)->toBeTrue();
});

it('reports is_published false for a scheduled post', function () {
    $post = Post::factory()->scheduled()->create();

    expect($post->is_published)->toBeFalse();
});
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `php artisan test --compact --filter=PostModelTest`
Expected: FAIL (classes `App\Models\Post` / `Author` / `Tag` and their factories do not exist yet).

- [ ] **Step 4: Update the authors migration**

Replace the body of `database/migrations/2026_07_09_145404_create_authors_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('social_media')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
```

- [ ] **Step 5: Update the posts migration**

Replace the body of `database/migrations/2026_07_09_145418_create_posts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->text('body');
            $table->string('image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

- [ ] **Step 6: Create the tags migration**

Run: `php artisan make:migration create_tags_table --no-interaction`
Then set its body (rename the generated file's timestamp is not required — use whatever timestamp is created, but ensure it sorts before the pivot):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
```

- [ ] **Step 7: Create the post_tag pivot migration**

Run: `php artisan make:migration create_post_tag_table --no-interaction`
Set its body (this migration must sort AFTER both `posts` and `tags`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};
```

- [ ] **Step 8: Create the HasUniqueSlug trait**

Create `app/Models/Concerns/HasUniqueSlug.php`:

```php
<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model): void {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    protected function generateUniqueSlug(): string
    {
        $base = Str::slug($this->{$this->sluggableSourceColumn()});
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($this->getKey(), fn ($query) => $query->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    abstract protected function sluggableSourceColumn(): string;
}
```

- [ ] **Step 9: Create the Author model**

Run: `php artisan make:model Author --no-interaction`
Then set `app/Models/Author.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'social_media',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

- [ ] **Step 10: Create the Tag model**

Run: `php artisan make:model Tag --no-interaction`
Then set `app/Models/Tag.php`:

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;
    use HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    protected function sluggableSourceColumn(): string
    {
        return 'name';
    }
}
```

- [ ] **Step 11: Create the Post model**

Run: `php artisan make:model Post --no-interaction`
Then set `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;
    use HasUniqueSlug;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'image',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected function isPublished(): Attribute
    {
        return Attribute::get(fn (): bool => $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now()));
    }

    protected function sluggableSourceColumn(): string
    {
        return 'title';
    }
}
```

- [ ] **Step 12: Create the factories**

Run: `php artisan make:factory AuthorFactory --no-interaction` and set `database/factories/AuthorFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Author>
 */
class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'social_media' => fake()->optional()->url(),
        ];
    }
}
```

Run: `php artisan make:factory TagFactory --no-interaction` and set `database/factories/TagFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            // slug auto-generated by the model on save
        ];
    }
}
```

Run: `php artisan make:factory PostFactory --no-interaction` and set `database/factories/PostFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'title' => fake()->sentence(),
            // slug auto-generated by the model on save
            'excerpt' => fake()->optional()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'image' => null,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['published_at' => now()->subDay()]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => ['published_at' => now()->addWeek()]);
    }
}
```

- [ ] **Step 13: Run the domain test to verify it passes**

Run: `php artisan test --compact --filter=PostModelTest`
Expected: PASS (8 assertions/tests green).

- [ ] **Step 14: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add blog domain layer (authors, posts, tags, slugs, scopes)"
```

---

### Task 2: Public blog index page

**Files:**
- Create: `app/Http/Controllers/PostController.php`
- Modify: `routes/web.php`
- Create: `resources/views/layouts/blog.blade.php`
- Create: `resources/views/blog/index.blade.php`
- Test: `tests/Feature/BlogIndexTest.php`

**Interfaces:**
- Consumes: `Post::published()`, `Post::factory()` states from Task 1.
- Produces:
  - Route `blog.index` → `GET /` → `PostController@index`.
  - `PostController@index(Request $request): View` passing `$posts` (paginator) and `$tag` (nullable `Tag`) to `blog.index`.
  - Blade layout `layouts.blog` with a `content` section and `title` section.

- [ ] **Step 1: Write the failing index test**

Create `tests/Feature/BlogIndexTest.php`:

```php
<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('shows published posts on the index', function () {
    $post = Post::factory()->published()->create(['title' => 'Live Post']);

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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=BlogIndexTest`
Expected: FAIL (route `blog.index` not defined).

- [ ] **Step 3: Create the controller**

Run: `php artisan make:controller PostController --no-interaction`
Then set `app/Http/Controllers/PostController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $tag = null;

        $posts = Post::published()
            ->with(['author', 'tags'])
            ->when($request->query('tag'), function ($query, string $tagSlug) use (&$tag): void {
                $tag = Tag::where('slug', $tagSlug)->firstOrFail();
                $query->whereHas('tags', fn ($relation) => $relation->whereKey($tag->getKey()));
            })
            ->latest('published_at')
            ->paginate(10);

        return view('blog.index', ['posts' => $posts, 'tag' => $tag]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published, 404);

        $post->load(['author', 'tags']);

        return view('blog.show', ['post' => $post]);
    }
}
```

- [ ] **Step 4: Define the routes**

Replace `routes/web.php`:

```php
<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
```

- [ ] **Step 5: Create the layout**

Create `resources/views/layouts/blog.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dev Blog')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-6">
            <a href="{{ route('blog.index') }}" class="text-xl font-semibold">Dev Blog</a>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-10">
        @yield('content')
    </main>
</body>
</html>
```

- [ ] **Step 6: Create the index view**

Create `resources/views/blog/index.blade.php`:

```blade
@extends('layouts.blog')

@section('title', $tag ? "Posts tagged {$tag->name}" : 'Dev Blog')

@section('content')
    @if ($tag)
        <p class="mb-6 text-sm text-gray-500">
            Showing posts tagged <span class="font-medium">{{ $tag->name }}</span> ·
            <a href="{{ route('blog.index') }}" class="text-amber-600 hover:underline">clear</a>
        </p>
    @endif

    @forelse ($posts as $post)
        <article class="mb-8 border-b border-gray-200 pb-8">
            @if ($post->image)
                <img src="{{ Storage::disk('public')->url($post->image) }}" alt="" class="mb-4 aspect-video w-full rounded object-cover">
            @endif
            <h2 class="text-2xl font-semibold">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-amber-600">{{ $post->title }}</a>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $post->author->name }} · {{ $post->published_at->format('M j, Y') }}
            </p>
            @if ($post->excerpt)
                <p class="mt-3 text-gray-700">{{ $post->excerpt }}</p>
            @endif
            @if ($post->tags->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($post->tags as $postTag)
                        <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">#{{ $postTag->name }}</a>
                    @endforeach
                </div>
            @endif
        </article>
    @empty
        <p class="text-gray-500">No posts yet.</p>
    @endforelse

    <div class="mt-8">
        {{ $posts->withQueryString()->links() }}
    </div>
@endsection
```

- [ ] **Step 7: Run the test to verify it passes**

Run: `php artisan test --compact --filter=BlogIndexTest`
Expected: PASS.

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add public blog index page"
```

---

### Task 3: Tag filtering on the index

**Files:**
- Test: `tests/Feature/BlogTagFilterTest.php`
- (No source changes expected — the `?tag=` branch was built in Task 2. This task proves and locks the behavior.)

**Interfaces:**
- Consumes: `PostController@index` tag branch, `blog.index` route.
- Produces: verified `?tag={slug}` filtering contract.

- [ ] **Step 1: Write the failing tag-filter test**

Create `tests/Feature/BlogTagFilterTest.php`:

```php
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
```

- [ ] **Step 2: Run the test**

Run: `php artisan test --compact --filter=BlogTagFilterTest`
Expected: PASS (behavior already implemented in Task 2). If it fails, fix `PostController@index` to match the contract before proceeding — do not weaken the test.

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "test: cover tag filtering on the blog index"
```

---

### Task 4: Single post page

**Files:**
- Create: `resources/views/blog/show.blade.php`
- Test: `tests/Feature/BlogPostPageTest.php`
- (Controller `show` and `blog.show` route were created in Task 2.)

**Interfaces:**
- Consumes: `PostController@show`, `blog.show` route, `Post::$is_published`.
- Produces: rendered single-post page; 404 for draft / scheduled / unknown slug.

- [ ] **Step 1: Write the failing single-post test**

Create `tests/Feature/BlogPostPageTest.php`:

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=BlogPostPageTest`
Expected: FAIL (view `blog.show` does not exist).

- [ ] **Step 3: Create the single-post view**

Create `resources/views/blog/show.blade.php`:

```blade
@extends('layouts.blog')

@section('title', $post->title)

@section('content')
    <article>
        <a href="{{ route('blog.index') }}" class="text-sm text-amber-600 hover:underline">← Back to all posts</a>

        <h1 class="mt-4 text-3xl font-bold">{{ $post->title }}</h1>

        <p class="mt-2 text-sm text-gray-500">
            @if ($post->author->social_media)
                <a href="{{ $post->author->social_media }}" class="hover:underline">{{ $post->author->name }}</a>
            @else
                {{ $post->author->name }}
            @endif
            · {{ $post->published_at->format('M j, Y') }}
        </p>

        @if ($post->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($post->tags as $postTag)
                    <a href="{{ route('blog.index', ['tag' => $postTag->slug]) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">#{{ $postTag->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($post->image)
            <img src="{{ Storage::disk('public')->url($post->image) }}" alt="" class="mt-6 w-full rounded">
        @endif

        <div class="mt-6 space-y-4 leading-relaxed">
            {!! $post->body !!}
        </div>
    </article>
@endsection
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact --filter=BlogPostPageTest`
Expected: PASS.

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add public single post page"
```

---

### Task 5: Filament AuthorResource

**Files:**
- Create (generated): `app/Filament/Resources/Authors/AuthorResource.php` + `Pages/` + `Schemas/AuthorForm.php` + `Tables/AuthorsTable.php`
- Modify: `app/Filament/Resources/Authors/Schemas/AuthorForm.php`
- Modify: `app/Filament/Resources/Authors/Tables/AuthorsTable.php`
- Test: `tests/Feature/Filament/AuthorResourceTest.php`

**Interfaces:**
- Consumes: `Author` model + factory; existing `AdminPanelProvider` auto-discovery.
- Produces: admin CRUD for authors at `/admin/authors`.

> **Note on generated paths:** After running the generator, confirm the exact created paths with `ls -R app/Filament/Resources/Authors`. Filament v5 names the form class `AuthorForm` and the table class `AuthorsTable` and wires them into `AuthorResource::form()`/`table()` automatically — you only edit those two class bodies.

- [ ] **Step 1: Write the failing resource test**

Create `tests/Feature/Filament/AuthorResourceTest.php`:

```php
<?php

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the authors list page', function () {
    Author::factory()->count(3)->create();

    livewire(ListAuthors::class)->assertOk();
});

it('creates an author', function () {
    livewire(CreateAuthor::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'social_media' => 'https://example.com/ada',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Author::class, [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=AuthorResourceTest`
Expected: FAIL (resource + pages do not exist).

- [ ] **Step 3: Generate the resource**

Run: `php artisan make:filament-resource Author --no-interaction`
Confirm paths: `ls -R app/Filament/Resources/Authors`

- [ ] **Step 4: Configure the form**

Set `app/Filament/Resources/Authors/Schemas/AuthorForm.php` `configure()` body (keep the generated namespace/class name):

```php
<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('social_media')
                    ->url()
                    ->maxLength(255),
            ]);
    }
}
```

- [ ] **Step 5: Configure the table**

Set `app/Filament/Resources/Authors/Tables/AuthorsTable.php` `configure()` body:

```php
<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --compact --filter=AuthorResourceTest`
Expected: PASS.

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Filament AuthorResource"
```

---

### Task 6: Filament TagResource

**Files:**
- Create (generated): `app/Filament/Resources/Tags/TagResource.php` + `Pages/` + `Schemas/TagForm.php` + `Tables/TagsTable.php`
- Modify: `app/Filament/Resources/Tags/Schemas/TagForm.php`
- Modify: `app/Filament/Resources/Tags/Tables/TagsTable.php`
- Test: `tests/Feature/Filament/TagResourceTest.php`

**Interfaces:**
- Consumes: `Tag` model (auto-slug from `name`) + factory.
- Produces: admin CRUD for tags at `/admin/tags`.

- [ ] **Step 1: Write the failing resource test**

Create `tests/Feature/Filament/TagResourceTest.php`:

```php
<?php

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the tags list page', function () {
    Tag::factory()->count(3)->create();

    livewire(ListTags::class)->assertOk();
});

it('creates a tag and auto-generates its slug', function () {
    livewire(CreateTag::class)
        ->fillForm(['name' => 'Laravel Tips'])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tag::class, [
        'name' => 'Laravel Tips',
        'slug' => 'laravel-tips',
    ]);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=TagResourceTest`
Expected: FAIL (resource + pages do not exist).

- [ ] **Step 3: Generate the resource**

Run: `php artisan make:filament-resource Tag --no-interaction`
Confirm paths: `ls -R app/Filament/Resources/Tags`

- [ ] **Step 4: Configure the form**

Set `app/Filament/Resources/Tags/Schemas/TagForm.php`:

```php
<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
```

- [ ] **Step 5: Configure the table**

Set `app/Filament/Resources/Tags/Tables/TagsTable.php`:

```php
<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --compact --filter=TagResourceTest`
Expected: PASS.

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Filament TagResource"
```

---

### Task 7: Filament PostResource

**Files:**
- Create (generated): `app/Filament/Resources/Posts/PostResource.php` + `Pages/` + `Schemas/PostForm.php` + `Tables/PostsTable.php`
- Modify: `app/Filament/Resources/Posts/Schemas/PostForm.php`
- Modify: `app/Filament/Resources/Posts/Tables/PostsTable.php`
- Test: `tests/Feature/Filament/PostResourceTest.php`

**Interfaces:**
- Consumes: `Post`, `Author`, `Tag` models + factories.
- Produces: admin CRUD for posts at `/admin/posts` with author select, tags multi-select (create-inline), image upload, rich-text body, publish datetime, and a published/draft filter.

- [ ] **Step 1: Write the failing resource test**

Create `tests/Feature/Filament/PostResourceTest.php`:

```php
<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Author;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the posts list page', function () {
    Post::factory()->count(3)->create();

    livewire(ListPosts::class)->assertOk();
});

it('creates a post with an author and tags', function () {
    $author = Author::factory()->create();
    $tag = Tag::factory()->create();

    livewire(CreatePost::class)
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
    livewire(CreatePost::class)
        ->fillForm([
            'title' => null,
            'body' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required', 'body' => 'required']);
});

it('loads the edit page and updates a post', function () {
    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getKey()])
        ->assertOk()
        ->fillForm(['title' => 'Updated Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Post::class, [
        'id' => $post->id,
        'title' => 'Updated Title',
    ]);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=PostResourceTest`
Expected: FAIL (resource + pages do not exist).

- [ ] **Step 3: Generate the resource**

Run: `php artisan make:filament-resource Post --no-interaction`
Confirm paths: `ls -R app/Filament/Resources/Posts`

- [ ] **Step 4: Configure the form**

Set `app/Filament/Resources/Posts/Schemas/PostForm.php`:

```php
<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from the title.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ]),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('posts'),
                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(500),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->helperText('Leave blank to keep as a draft.'),
            ]);
    }
}
```

- [ ] **Step 5: Configure the table**

Set `app/Filament/Resources/Posts/Tables/PostsTable.php`:

```php
<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->badge(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Draft'),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label('Publish state')
                    ->placeholder('All posts')
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts & scheduled')
                    ->queries(
                        true: fn (Builder $query): Builder => $query
                            ->whereNotNull('published_at')
                            ->where('published_at', '<=', now()),
                        false: fn (Builder $query): Builder => $query
                            ->where(fn (Builder $inner): Builder => $inner
                                ->whereNull('published_at')
                                ->orWhere('published_at', '>', now())),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --compact --filter=PostResourceTest`
Expected: PASS.

- [ ] **Step 7: Run the full suite**

Run: `php artisan test --compact`
Expected: PASS (all tasks green).

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Filament PostResource"
```

---

## Notes for the implementer

- **Vite in tests:** feature tests that render Blade call `@vite`; every public-page test file already calls `$this->withoutVite()` in `beforeEach` so no manifest/build is needed. Do not remove it.
- **Filament panel access in tests:** the app runs in the `testing` environment, where Filament permits any authenticated user; `actingAs(User::factory()->create())` is sufficient. No `FilamentUser` contract is required for v1.
- **Generated resource file names:** if a generated class name differs from what a test imports (e.g. `ListPosts`), reconcile by trusting the generated name and updating the test's `use` import — do not rename Filament's generated classes.
- **Do not build front-end assets** as part of tests. A human runs `npm run build` / `composer run dev` to view pages in a browser.
