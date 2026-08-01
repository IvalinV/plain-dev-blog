<?php

namespace App\Providers;

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
        Head::defaults(function (HeadBuilder $head) {
            $head
                ->title('Plain Dev Blog', suffix: ' - Ivalin Venkov')
                ->description('Plain Dev Blog — articles and tutorials on software development.')
                ->canonical()
                ->og(siteName: 'Plain Dev Blog', type: OgType::Website)
                ->ogImage(asset('images/blog_social_image.jpg'))
                ->twitterImage(asset('images/blog_social_image.jpg'))
                ->searchableByRobots();
        });
    }
}
