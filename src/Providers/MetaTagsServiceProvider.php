<?php

namespace Woren951\MetaTags\Providers;

use Illuminate\Support\ServiceProvider;
use Woren951\MetaTags\Managers\MetaTags;

class MetaTagsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('meta-tags', MetaTags::class);
    }
}
