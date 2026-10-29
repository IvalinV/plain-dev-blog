<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;
use UnexpectedValueException;

class LaravelCloudApi
{
    /**
     * Make GET request to provided endpoint.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function get(string $url, array $parameters = []): array
    {
        $token = config('services.laravel_cloud.api_key');
        $baseUrl = config('services.laravel_cloud.base_url');

        if (! is_string($token) || $token === '' || ! is_string($baseUrl) || $baseUrl === '') {
            throw new InvalidArgumentException('Laravel Cloud API is not configured.');
        }

        $data = Http::connectTimeout(3)
            ->timeout(10)
            ->retry([100, 500, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->withToken($token)
            ->get(rtrim($baseUrl, '/').'/'.ltrim($url, '/'), $parameters)
            ->throw()
            ->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException('Laravel Cloud returned an invalid response.');
        }

        return $data;
    }
}
