# Filament-Native Dev Blog — Design (v1 "Lean core")

**Date:** 2026-07-09
**Status:** Approved design, pending spec review

## Context

Fresh Laravel 13.19 + Filament v5 project (PHP 8.4). Admin panel lives at
`/admin` (amber theme). Only the default `User` model exists. Two migrations
were scaffolded outside the initial brainstorm (`authors`, `posts`); this
design blends them with the brainstormed feature set.

The original intent was to install `binshops/laravel-blog`, but that package —
even `dev-master` — depends transitively on `cviebrock/eloquent-sluggable`,
which has no Laravel 13 release. It is not installable here, so the blog is
built natively instead. **No slug package is used**; slugs are generated with
`Str::slug` plus an in-app uniqueness guard.

## Scope (v1)

Lean core: posts with a single taxonomy (tags), draft/published state, a
Filament authoring UI, and a minimal public site (index + single post). No
comments, no SEO meta fields, no multi-panel work.

## Data Model

### `authors`
| column | type | notes |
|---|---|---|
| id | pk | |
| name | string | |
| email | string, unique | |
| social_media | string, nullable | changed from scaffold: now nullable |
| timestamps | | |

### `posts`
| column | type | notes |
|---|---|---|
| id | pk | |
| author_id | foreignId, constrained → authors | |
| title | string | |
| slug | string, unique | auto-generated from title, editable, unique |
| excerpt | text, nullable | optional; falls back to trimmed, tag-stripped body |
| body | text | sanitized HTML from Filament RichEditor |
| image | string, nullable | path on the `public` disk |
| published_at | timestamp, nullable | null = draft; past value = live |
| timestamps | | |

Replaced the scaffolded `published` boolean with `published_at` — single
source of truth for both publish state and publish date.

### `tags`
| column | type | notes |
|---|---|---|
| id | pk | |
| name | string | |
| slug | string, unique | auto-generated from name |
| timestamps | | |

### `post_tag` (pivot)
`post_id`, `tag_id` (both foreignId, constrained; composite index).

## Models

- **`Author`** — `hasMany(Post)`.
- **`Post`** — `belongsTo(Author)`, `belongsToMany(Tag)`.
  - `scopePublished($q)` → `whereNotNull('published_at')->where('published_at', '<=', now())`.
  - `getIsPublishedAttribute(): bool`.
  - Slug auto-set on saving when blank, via a `saving` model hook using
    `Str::slug(title)` with a uniqueness suffix (`-2`, `-3`, …).
- **`Tag`** — `belongsToMany(Post)`; slug auto-set on saving from `name`.

Factories for all three, with `published` / `draft` states on `PostFactory`.
No seeder in v1 unless requested.

## Filament Admin (`/admin`)

Auto-discovered by the existing `AdminPanelProvider` (amber theme untouched).

- **`AuthorResource`** — table (name, email, post count); form (name, email,
  social_media).
- **`TagResource`** — table (name, post count); form (name; slug auto).
- **`PostResource`**
  - Table columns: title, author, tag count, published-state badge,
    `published_at`.
  - Table filters: published / draft; by tag.
  - Form: title; slug (auto, editable); `Select` author; `FileUpload` image
    (public disk); `RichEditor` body; `Textarea` excerpt; `DateTimePicker`
    `published_at`; tags `Select` (multiple, create-inline).

## Public Site (Blade + Tailwind v4)

- **`/`** — index: paginated published posts, newest first (image thumb,
  title, excerpt, tags, date). Supports `?tag={slug}` to filter to one tag.
- **`/blog/{slug}`** — single post: image, title, author (+ social link if
  present), date, tags, rendered body.
- **`PostController`** (`index`, `show`).
  - `index`: `Post::published()->latest('published_at')->paginate()`, optional
    tag filter via query string.
  - `show`: route-model-binding on `slug`, constrained to published posts;
    unpublished or future-dated → 404.
- Root route (`/`) replaces the default `welcome` view.

## Rendering & Safety

`body` is stored as HTML already sanitized by Filament's RichEditor on save,
rendered with `{!! !!}`. Authors are trusted admins only. No user-supplied
HTML enters the render path.

## Testing (Pest, feature-first)

- Index shows published posts; hides drafts and future-dated posts.
- `?tag=` filter returns only matching posts.
- Single-post page renders a published post; 404 for draft / future-dated /
  unknown slug.
- Slug auto-generation and uniqueness (collision → suffixed).
- `Post::published()` scope boundary (published_at exactly `now()` and future).
- Filament `PostResource` create + edit smoke tests (Livewire).

## Out of Scope (v1)

Comments, SEO meta fields, multi-author permissions/roles, RSS feed,
categories, search, image variants/thumbnails generation. Revisit later.
