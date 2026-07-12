<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('auto-generates a unique slug from the name', function () {
    $first = Author::factory()->create(['name' => 'Jane Doe', 'slug' => null]);
    $second = Author::factory()->create(['name' => 'Jane Doe', 'slug' => null]);

    expect($first->slug)->toBe('jane-doe');
    expect($second->slug)->toBe('jane-doe-2');
});

it('persists image and bio', function () {
    Author::factory()->create([
        'name' => 'Ada Lovelace',
        'image' => 'authors/ada.jpg',
        'bio' => 'Mathematician and writer.',
    ]);

    assertDatabaseHas('authors', [
        'slug' => 'ada-lovelace',
        'image' => 'authors/ada.jpg',
        'bio' => 'Mathematician and writer.',
    ]);
});
