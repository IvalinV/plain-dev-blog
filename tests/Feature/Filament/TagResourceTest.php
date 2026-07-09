<?php

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('loads the tags list page', function () {
    Tag::factory()->count(3)->create();

    Livewire::test(ListTags::class)->assertOk();
});

it('creates a tag and auto-generates its slug', function () {
    Livewire::test(CreateTag::class)
        ->fillForm(['name' => 'Laravel Tips'])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tag::class, [
        'name' => 'Laravel Tips',
        'slug' => 'laravel-tips',
    ]);
});
