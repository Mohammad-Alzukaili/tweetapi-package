<?php

namespace Mawdoo3\Tweets;

use Illuminate\Support\ServiceProvider;
use Mawdoo3\Tweets\Console\GetTweets;
use Mawdoo3\Tweets\Console\CheckTweets;
class TweetsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__."/routes/web.php");
        $this->loadViewsFrom(__DIR__."/views/","tweets");
        $this->loadMigrationsFrom(__DIR__."/database/migrations");
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([GetTweets::class,CheckTweets::class]);
    }
}
