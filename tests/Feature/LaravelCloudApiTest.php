<?php

use App\Filament\Widgets\CloudSpendingOverview;
use App\Services\LaravelCloudApi;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.laravel_cloud.api_key', 'test-token');
    config()->set('services.laravel_cloud.base_url', 'https://cloud.laravel.com/api');
});

it('gets a cloud api response with query parameters', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage*' => Http::response(['data' => ['ok' => true]]),
    ]);

    $response = app(LaravelCloudApi::class)->get('usage', ['period' => 'month']);

    expect($response)->toBe(['data' => ['ok' => true]]);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://cloud.laravel.com/api/usage?period=month'
            && $request->hasHeader('Authorization', 'Bearer test-token');
    });
});

it('throws when the cloud api returns an error', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([], 503),
    ]);

    expect(fn () => app(LaravelCloudApi::class)->get('usage'))
        ->toThrow(RequestException::class);
});

it('retries transient cloud api failures', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::sequence()
            ->push([], 503)
            ->push(['data' => ['ok' => true]]),
    ]);

    expect(app(LaravelCloudApi::class)->get('usage'))
        ->toBe(['data' => ['ok' => true]]);

    Http::assertSentCount(2);
});

it('throws when the cloud api is not configured', function () {
    config()->set('services.laravel_cloud.api_key', null);

    expect(fn () => app(LaravelCloudApi::class)->get('usage'))
        ->toThrow(InvalidArgumentException::class);
});

it('renders an unavailable state when cloud spending cannot be loaded', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::failedConnection(),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('renders cloud spending in euros using the configured conversion rate', function () {
    config()->set('services.laravel_cloud.usd_to_eur_rate', 0.90);

    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([
            'data' => ['summary' => ['current_spend_cents' => 1000]],
        ]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('€9.00')
        ->assertDontSee('Recent Visitors');
});

it('renders an unavailable state when the cloud api response is malformed', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response(['data' => []]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('renders an unavailable state for negative spending values', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([
            'data' => ['summary' => ['current_spend_cents' => -100]],
        ]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('renders an unavailable state for non-integer spending values', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([
            'data' => ['summary' => ['current_spend_cents' => '1e3']],
        ]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('renders an unavailable state for oversized spending values', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([
            'data' => ['summary' => ['current_spend_cents' => '999999999999999999999999999999999']],
        ]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('renders an unavailable state for an invalid conversion rate', function () {
    config()->set('services.laravel_cloud.usd_to_eur_rate', 'invalid');

    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response([
            'data' => ['summary' => ['current_spend_cents' => 1000]],
        ]),
    ]);

    Livewire::test(CloudSpendingOverview::class)
        ->assertSee('Unavailable');
});

it('throws when the cloud api returns a non-array response', function () {
    Http::fake([
        'https://cloud.laravel.com/api/usage' => Http::response('invalid'),
    ]);

    expect(fn () => app(LaravelCloudApi::class)->get('usage'))
        ->toThrow(UnexpectedValueException::class);
});
