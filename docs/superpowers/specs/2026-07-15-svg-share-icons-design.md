# SVG Share Icons (drop Font Awesome CDN)

**Date:** 2026-07-15
**Status:** Approved

## Goal

Replace the Font Awesome icon-font share buttons on the blog post page with
self-contained inline SVGs, and remove the external Font Awesome CDN dependency
entirely. The SVGs adopt the same monochrome style already used for the social
links on the authors page.

## Motivation

The share buttons are the **only** consumer of Font Awesome in the app
(`layouts/blog.blade.php:55` loads `//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css`),
and they render on **only one** page (`blog/show.blade.php`). Dropping the CDN
removes a third-party request on every post page, works offline, and gives us
crisp, self-hosted icons.

## Current State

- Package: `kudashevs/laravel-share-buttons`, rendered via `ShareButtons::currentPage(...)` in `resources/views/blog/show.blade.php:73`.
- Active buttons: `twitter`, `linkedin`, `reddit`, `copylink` (only these four are rendered).
- Icons: Font Awesome `<span class="fab fa-...">` defined in the `templates` array of `config/share-buttons.php`.
- Colours/layout: `resources/css/share-buttons.css` (imported by `resources/css/app.css`).
- Click behaviour: `resources/js/share-buttons.js` opens a popup for network buttons and copies the URL for `#clip`. It identifies the target by walking **at most one** level up from `e.target` (checks `e.target` and `e.target.parentElement` for the `social-button` class).

## Design

### 1. Icon templates — `config/share-buttons.php`

Replace the FA `<span>` in the `twitter`, `linkedin`, `reddit`, and `copylink`
template entries. Each becomes an anchor styled like the authors page social
links, with an `sr-only` label and an inline SVG:

```html
<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel>
    <span class="sr-only">X</span>
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="..."/></svg>
</a>
```

Requirements that MUST be preserved on each anchor:

- `class="social-button:class"` — the `social-button` class is required by `share-buttons.js`; `:class` is substituted by the package.
- `:url`, `:id`, `:title`, `:rel` placeholders — used by the package's templater.
- `id="clip"` on the `copylink` template — required by the JS copy handler.
- `target="_blank"` where the original template had it (none of the four in scope currently do, so no change here).

Icon sources (all `viewBox="0 0 24 24"`, `fill="currentColor"`):

- **X / twitter** — reuse the exact path already on the authors page.
- **linkedin** — reuse the exact path already on the authors page.
- **reddit** — Reddit brand glyph (Simple Icons).
- **copylink** — filled "link" icon (Heroicons solid).

Icon size: `size-5` (1.25rem) — smaller than the authors page `size-6`.

### 2. Container layout — `config/share-buttons.php`

Change `block_prefix` to lay the buttons out as a horizontal row:

```php
'block_prefix' => '<div id="social-buttons" class="flex items-center gap-x-5">',
```

`block_suffix` stays `</div>`.

### 3. Remove the CDN — `resources/views/layouts/blog.blade.php`

Delete the Font Awesome `<link>` (line 55).

### 4. Remove obsolete CSS

- Delete `resources/css/share-buttons.css` (its `.fa-*` colour rules and
  `#social-buttons` sizing rules are all superseded by Tailwind utilities and
  `currentColor`).
- Remove the `@import './share-buttons.css';` line from `resources/css/app.css`.

### Click-through correctness

The JS handler only inspects `e.target` and `e.target.parentElement`. With a
nested `<svg><path>`, a click on the path would be two levels below the anchor
and would miss the `social-button` class, breaking the popup/copy behaviour.

Fix without touching the JS: add the `pointer-events-none` utility to every
`<svg>` so clicks on the icon area register on the `<a>` itself. This keeps
`e.target === <a>`, which the handler already handles correctly.

## Testing

Update `tests/Feature/BlogPostPageTest.php` to assert the rendered post page:

1. contains `<svg` within the `#social-buttons` block,
2. still contains the twitter, linkedin, and reddit share hrefs,
3. still contains the `id="clip"` copy-link anchor,
4. does **not** reference the Font Awesome CDN (`font-awesome`) or any `fa-` class.

Run: `php artisan test --compact --filter=BlogPostPage`.

## Out of Scope

- The ~14 unused platform templates in `config/share-buttons.php` keep their
  Font Awesome markup. They are never rendered, so they cause no visible
  breakage; converting them is unnecessary work (YAGNI).
- No brand colours: icons render gray with light/dark hover matching the authors
  page, per the approved direction.