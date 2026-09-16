<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }

        Head::defaults(function (HeadBuilder $head) {
            $appUrl = rtrim((string) config('app.url'), '/');

            $head
                ->title('Plain Dev Blog', suffix: ' - Ivalin Venkov')
                ->description('Plain Dev Blog — articles and tutorials on software development.')
                ->canonical()
                ->og(siteName: 'Plain Dev Blog', type: OgType::Website, url: $appUrl)
                ->ogImage($appUrl.'/images/blog_social_image.jpg')
                ->twitterImage($appUrl.'/images/blog_social_image.jpg')
                ->searchableByRobots();
        });
    }
}
