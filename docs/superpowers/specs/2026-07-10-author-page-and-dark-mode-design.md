# Author Page + Dark Mode — Design

**Date:** 2026-07-10
**Branch:** feature/filament-dev-blog

## Overview

Two independent features for the Plain Dev Blog:

1. **Author page** — a public page showing short information about an author (avatar, name, bio, social link) plus their published posts. Add a new `image` column (and a `bio` column) to `authors`. On every post page, clicking the author name links to this page.
2. **Dark mode** — a class-based Tailwind v4 dark theme with a header toggle that persists the user's choice and falls back to the OS preference on first visit.

---

## Feature 1 — Author page

### Database

One migration adding three columns to the existing `authors` table:

- `slug` — `string`, unique. Auto-generated from `name`.
- `image` — `string`, nullable. Stores the avatar path on the `public` disk.
- `bio` — `text`, nullable. Short descriptive text.

Because the columns are added to an existing table, run `php artisan migrate:fresh --seed` (or reseed authors) so existing rows get slugs. Authors are created via factory in tests, not seeded in `DatabaseSeeder`.

### Model — `App\Models\Author`

- `use HasUniqueSlug;` (existing trait, same as `Post`).
- Implement `protected function sluggableSourceColumn(): string { return 'name'; }`.
- Add `slug`, `image`, `bio` to `$fillable`.
- Existing `posts()` `HasMany` relation stays. Published posts are obtained by applying `Post::scopePublished()` to the relation in the controller (e.g. `$author->posts()->published()->latest('published_at')->get()`).

### Route & controller

```php
Route::get('/authors/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');
```

`AuthorController@show(Author $author): View`:
- Loads the author's published posts, latest first, eager-loading `tags`.
- Returns `authors.show` with `author` and `posts`.
- Route-model binding on `slug` yields a 404 for unknown slugs automatically.

### View — `resources/views/authors/show.blade.php`

- Extends `layouts.blog`.
- `@section('title', $author->name)` and a meta description derived from the bio.
- Avatar from `Storage::disk('public')->url($author->image)` with a fallback (initials or a neutral placeholder) when `image` is null.
- Author name, bio, and external social link (`social_media`) when present.
- A list of the author's published posts reusing the card markup/style from `blog/index.blade.php`.
- "← Back to all posts" link consistent with the post page.

### Post page change — `resources/views/blog/show.blade.php`

- The author name currently links to the external `social_media` URL. Change it to link to `route('authors.show', $post->author->slug)` with `rel="author"`.
- The external social link is presented on the author page instead, not on the post page.

### Filament — `AuthorForm`

Mirror `PostForm` conventions. Add to the existing components:

- `TextInput::make('slug')->helperText('Leave blank to auto-generate from the name.')->unique(ignoreRecord: true)->maxLength(255)`.
- `FileUpload::make('image')->image()->disk('public')->directory('authors')`.
- `Textarea::make('bio')->rows(3)->maxLength(500)`.

### Factory & tests

- `AuthorFactory`: add `slug` (unique, derived), `image => null`, `bio => fake()->optional()->sentence()`.
- New `AuthorPageTest` (Pest feature test):
  - renders the author's name, bio, and social link;
  - lists the author's published posts;
  - does not list draft/unpublished posts;
  - returns 404 for an unknown slug.
- Update `BlogPostPageTest`: assert the author name links to `route('authors.show', ...)`.

---

## Feature 2 — Dark mode

Tailwind v4, class-based, with a toggle that persists and defaults to the OS preference.

### Variant

Add to `resources/css/app.css`:

```css
@custom-variant dark (&:where(.dark, .dark *));
```

This makes `dark:` utilities respond to a `.dark` class on `<html>` rather than the media query, enabling a manual toggle.

### No-flash initialization

An inline `<script>` in `<head>` of `layouts/blog.blade.php`, executed before paint, to prevent a flash of the wrong theme:

```html
<script>
    (function () {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

### Toggle

- A button in the header (`layouts/blog.blade.php`) with an accessible label and sun/moon indication.
- JS in `resources/js/app.js` toggles the `.dark` class on `<html>` and writes `localStorage.theme` (`'dark'` / `'light'`).

### Styling

Add `dark:` variants across:

- `layouts/blog.blade.php` — body background/text, header background/border, the site title, and the toggle button.
- `blog/index.blade.php` — page background inherited; card backgrounds/borders, headings, meta text, tag chips, pagination.
- `blog/show.blade.php` — headings, meta text, "back" link, tag chips, and the prose `[&_...]` block (blockquote borders, code block already dark — keep readable in both themes).
- `authors/show.blade.php` — same palette as the other pages.

The `tailwindcss-development` skill will be invoked during implementation for the dark-variant work.

### Build note

Frontend changes require `npm run build` (or `npm run dev` / `composer run dev`) to take effect.

---

## Out of scope

- No dark-mode theming of the Filament admin panel (it ships its own).
- No author avatar image processing/resizing beyond Filament's `FileUpload`.
- No pagination on the author page's post list (author post counts are expected to be small); revisit if needed.
