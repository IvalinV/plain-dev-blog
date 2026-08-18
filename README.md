# Plain Dev Blog

A clean, SEO-friendly developer blog built with Laravel 13. Content is managed through a Filament v5 admin panel and rendered as a fast, minimal public site with Blade and Tailwind CSS.

## Features

- **Blog posts** with slugs, excerpts, cover images, and publish scheduling
- **Authors** with bios, avatars, and social media links
- **Tags** with post filtering (`?tag=...`)
- **Filament v5 admin panel** for managing posts, authors, and tags
- **SEO built in** — meta tags, Open Graph and Twitter cards via [laravel/head](https://github.com/laravel/head), plus dynamic `sitemap.xml` and `robots.txt`
- **Social share buttons** via [kudashevs/laravel-share-buttons](https://github.com/kudashev/laravel-share-buttons)
- **S3-compatible image storage** for post and author images
- Pagination, Blade layouts, and Tailwind CSS v4 styling

## Tech Stack

- PHP 8.3+ / Laravel 13
- Filament 5 (admin panel)
- Tailwind CSS 4 + Vite
- MySQL (SQLite also supported)
- Pest 4 (testing)

## Getting Started

```bash
# Install dependencies, generate app key, run migrations, and build assets
composer setup

# Create an admin user for the Filament panel
php artisan make:filament-user

# Start the dev environment (server, queue, logs, and Vite)
composer run dev
```

The public site is served at `/` and the admin panel at `/admin`.

## Configuration

Key environment variables beyond the Laravel defaults:

| Variable | Purpose |
| --- | --- |
| `DB_*` | Database connection (MySQL by default) |
| `AWS_*` | S3 credentials used for post/author image storage |

## Testing

```bash
php artisan test --compact
```

## Code Quality

```bash
# Format PHP with Laravel Pint
vendor/bin/pint
```

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
