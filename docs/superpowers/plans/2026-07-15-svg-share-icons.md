# SVG Share Icons Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the Font Awesome icon-font share buttons on the blog post page with self-contained inline SVGs (authors page style) and remove the Font Awesome CDN dependency.

**Architecture:** The `kudashevs/laravel-share-buttons` package renders each button from a string template in `config/share-buttons.php`. We swap the Font Awesome `<span>` in the four rendered templates (twitter, linkedin, reddit, copylink) for inline `<svg>` markup styled with Tailwind utilities and `currentColor`, then remove the now-unused CDN link and CSS file. Each change leaves the page fully working, so tasks are shippable in order.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS v4, `kudashevs/laravel-share-buttons`, Pest v4.

## Global Constraints

- Do NOT add or remove Composer/npm dependencies (CLAUDE.md).
- Each button anchor MUST keep `class="social-button:class"` — the `social-button` class is required by `resources/js/share-buttons.js`; `:class` is a package placeholder.
- Each button template MUST keep the package placeholders `:url`, `:id`, `:title`, `:rel`.
- The copylink template MUST keep `id="clip"` — required by the JS copy handler.
- Every `<svg>` MUST carry the `pointer-events-none` utility so clicks register on the `<a>` (the JS only walks one level up from `e.target`).
- Icons: `viewBox="0 0 24 24"`, `fill="currentColor"`, `class="size-5 pointer-events-none"`, monochrome (no brand colours), matching the authors page treatment.
- Run Pint after touching PHP: `vendor/bin/pint --dirty --format agent`.

---

### Task 1: Swap the four button templates to inline SVG + row layout

**Files:**
- Modify: `config/share-buttons.php` (the `block_prefix` value and the `twitter`, `linkedin`, `reddit`, `copylink` entries of the `templates` array)
- Test: `tests/Feature/BlogPostPageTest.php` (extend the existing `renders social share buttons` test)

**Interfaces:**
- Consumes: nothing new.
- Produces: rendered `#social-buttons` block containing four `<a class="social-button ...">` anchors, each with an inline `<svg>`; copylink anchor has `id="clip"`.

- [ ] **Step 1: Update the failing test**

Replace the existing `renders social share buttons` test in `tests/Feature/BlogPostPageTest.php` with:

```php
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
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="renders social share buttons"`
Expected: FAIL — the page still renders Font Awesome `<span>`s, so `assertSee('<svg', ...)` fails.

- [ ] **Step 3: Update `block_prefix`**

In `config/share-buttons.php`, change:

```php
'block_prefix' => '<div id="social-buttons">',
```

to:

```php
'block_prefix' => '<div id="social-buttons" class="flex items-center gap-x-5">',
```

- [ ] **Step 4: Replace the `twitter` template**

In `config/share-buttons.php` → `templates`, change the `twitter` line to:

```php
'twitter' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">X</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z"/></svg></a>',
```

- [ ] **Step 5: Replace the `linkedin` template**

Change the `linkedin` line to:

```php
'linkedin' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">LinkedIn</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M20.447 20.452H16.89v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.346V9h3.414v1.561h.049c.476-.9 1.637-1.85 3.37-1.85 3.604 0 4.268 2.372 4.268 5.456v6.285ZM5.337 7.433a2.063 2.063 0 1 1 0-4.126 2.063 2.063 0 0 1 0 4.126ZM7.119 20.452H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003Z"/></svg></a>',
```

- [ ] **Step 6: Replace the `reddit` template**

Change the `reddit` line to:

```php
'reddit' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">Reddit</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-6.995 4.87-3.865 0-6.994-2.176-6.994-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg></a>',
```

- [ ] **Step 7: Replace the `copylink` template**

Change the `copylink` line to (note `id="clip"` is preserved):

```php
'copylink' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" id="clip":title:rel><span class="sr-only">Copy link</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.389 4.267a.75.75 0 0 1 1-.353 5.25 5.25 0 0 1 1.449 8.45l-4.5 4.5a5.25 5.25 0 1 1-7.424-7.424l1.757-1.757a.75.75 0 1 1 1.06 1.06l-1.757 1.757a3.75 3.75 0 1 0 5.304 5.304l4.5-4.5a3.75 3.75 0 0 0-1.035-6.037.75.75 0 0 1-.354-1Z"/></svg></a>',
```

- [ ] **Step 8: Rebuild assets**

Run: `npm run build`
Expected: build succeeds; Tailwind picks up the new utility classes (`size-5`, `pointer-events-none`, `gap-x-5`, gray classes) from the config file.

Note: Tailwind v4 auto-detects the `config/` directory (it is not gitignored). If the icons render unstyled after the build, add `@source '../../config/share-buttons.php';` to `resources/css/app.css` and rebuild — but verify first before adding it.

- [ ] **Step 9: Run test to verify it passes**

Run: `php artisan test --compact --filter="renders social share buttons"`
Expected: PASS.

- [ ] **Step 10: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add config/share-buttons.php tests/Feature/BlogPostPageTest.php
git commit -m "Feature: Render share buttons as inline SVG icons"
```

---

### Task 2: Remove the Font Awesome CDN link

**Files:**
- Modify: `resources/views/layouts/blog.blade.php` (delete line 55, the Font Awesome `<link>`)
- Test: `tests/Feature/BlogPostPageTest.php` (add a Font-Awesome-absence assertion)

**Interfaces:**
- Consumes: the SVG buttons from Task 1 (so removing the icon font is safe).
- Produces: post page HTML with no reference to Font Awesome.

- [ ] **Step 1: Add the failing assertion**

Append to the `renders social share buttons` test in `tests/Feature/BlogPostPageTest.php`, after the existing assertions:

```php
    // Font Awesome CDN is gone; icons are self-hosted SVGs.
    $response->assertDontSee('font-awesome', escape: false);
    $response->assertDontSee('fa-', escape: false);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="renders social share buttons"`
Expected: FAIL — `assertDontSee('font-awesome', ...)` fails because the CDN `<link>` is still present.

- [ ] **Step 3: Delete the CDN link**

In `resources/views/layouts/blog.blade.php`, delete this line (currently line 55):

```html
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter="renders social share buttons"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/blog.blade.php tests/Feature/BlogPostPageTest.php
git commit -m "Feature: Remove Font Awesome CDN dependency"
```

---

### Task 3: Delete the obsolete share-buttons CSS

**Files:**
- Delete: `resources/css/share-buttons.css`
- Modify: `resources/css/app.css` (remove the `@import './share-buttons.css';` line)

**Interfaces:**
- Consumes: nothing — the file's `.fa-*` colour rules and `#social-buttons` sizing are fully superseded by Tailwind utilities and `currentColor` from Task 1.
- Produces: a smaller CSS bundle with no dead rules.

- [ ] **Step 1: Remove the import**

In `resources/css/app.css`, delete this line (currently line 2):

```css
@import './share-buttons.css';
```

- [ ] **Step 2: Delete the CSS file**

Run: `git rm resources/css/share-buttons.css` (if untracked, use `rm resources/css/share-buttons.css`)
Expected: file removed.

- [ ] **Step 3: Rebuild assets**

Run: `npm run build`
Expected: build succeeds with no missing-import error.

- [ ] **Step 4: Run the full post-page test file to confirm nothing regressed**

Run: `php artisan test --compact --filter=BlogPostPage`
Expected: PASS (all tests in `BlogPostPageTest.php`).

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css resources/css/share-buttons.css
git commit -m "Refactor: Remove obsolete share-buttons stylesheet"
```

---

## Self-Review

**Spec coverage:**
- Icon templates → SVG (spec §1) → Task 1 steps 4–7. ✔
- Container layout `block_prefix` (spec §2) → Task 1 step 3. ✔
- Remove CDN link (spec §3) → Task 2. ✔
- Delete CSS + import (spec §4) → Task 3. ✔
- Click-through `pointer-events-none` fix → Global Constraints + every SVG template in Task 1. ✔
- Testing assertions 1–4 (spec Testing) → svg (T1 s1), share hrefs (T1 s1), `id="clip"` (T1 s1), no font-awesome/`fa-` (T2 s1). ✔
- Icon sizing `size-5` (revised design) → all four templates. ✔
- Authors page monochrome style → gray/hover classes on each `<a>`. ✔

**Placeholder scan:** No TBD/TODO/"handle edge cases"; every code step has complete markup. ✔

**Type/name consistency:** `social-button:class`, `id="clip"`, `size-5 pointer-events-none`, and the gray class set are identical across all four templates and match the test assertions (`<svg`, `id="clip"`, `linkedin.com/sharing/share-offsite`, `reddit.com/submit`). ✔