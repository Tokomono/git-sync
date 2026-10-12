<?php

namespace Tokomono\GitSync\Providers;

use Illuminate\Support\ServiceProvider;
use Tokomono\GitSync\Contracts\GitProviderInterface;
use Tokomono\GitSync\Services\GitHubProvider;
use Tokomono\GitSync\Services\BitbucketProvider;

class GitSyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/git-sync.php', 'git-sync');

        $this->app->bind(GitProviderInterface::class, function ($app) {
            $provider = config('git-sync.default_provider');

            if ($provider === 'github') {
                return new GitHubProvider(config('git-sync.providers.github'));
            }

            if ($provider === 'bitbucket') {
                return new BitbucketProvider(config('git-sync.providers.bitbucket'));
            }

            throw new \Exception("Unsupported Git provider config: {$provider}. You can bind your own implementation to GitProviderInterface.");
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/git-sync.php' => config_path('git-sync.php'),
            ], 'git-sync-config');

            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }
}
