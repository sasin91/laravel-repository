<?php

namespace Sasin91\LaravelRepository;

use Illuminate\Support\ServiceProvider;
use Sasin91\LaravelRepository\Console\Commands\RepositoryMakeCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([RepositoryMakeCommand::class]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            RepositoryMakeCommand::class
        ];
    }
}
