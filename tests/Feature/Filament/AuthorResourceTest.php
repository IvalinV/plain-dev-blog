<?php

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the authors list page', function () {
    Author::factory()->count(3)->create();

    Livewire::test(ListAuthors::class)->assertOk();
});

it('creates an author', function () {
    Livewire::test(CreateAuthor::class)
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
