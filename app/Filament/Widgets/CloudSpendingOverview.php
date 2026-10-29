<?php

namespace App\Filament\Widgets;

use App\Services\LaravelCloudApi;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Arr;
use Throwable;
use UnexpectedValueException;

class CloudSpendingOverview extends StatsOverviewWidget
{
    protected function getHeading(): ?string
    {
        return 'Cloud Spending';
    }

    protected function getDescription(): ?string
    {
        return 'Current Laravel Cloud usage and spending.';
    }

    protected function getStats(): array
    {
        try {
            $data = app(LaravelCloudApi::class)->get('usage');
            $spendCents = Arr::get($data, 'data.summary.current_spend_cents');
            $conversionRate = config('services.laravel_cloud.usd_to_eur_rate');
            $validatedSpendCents = filter_var($spendCents, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);
            $validatedConversionRate = filter_var($conversionRate, FILTER_VALIDATE_FLOAT);

            if (
                $validatedSpendCents === false
                || $validatedConversionRate === false
                || ! is_finite((float) $validatedConversionRate)
                || $validatedConversionRate <= 0
            ) {
                throw new UnexpectedValueException('Laravel Cloud returned an invalid spending value.');
            }

            $totalSpentEur = ((float) $validatedSpendCents / 100) * (float) $validatedConversionRate;

            if (! is_finite($totalSpentEur)) {
                throw new UnexpectedValueException('Laravel Cloud returned an unrepresentable spending value.');
            }

            return [
                Stat::make('Total Spent', '€'.number_format($totalSpentEur, 2)),
            ];
        } catch (Throwable) {
            return [
                Stat::make('Total Spent', 'Unavailable')
                    ->description('Laravel Cloud spending is currently unavailable.'),
            ];
        }
    }
}
