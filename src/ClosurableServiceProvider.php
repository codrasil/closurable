<?php

namespace Codrasil\Closurable;

use Codrasil\Closurable\Console\Commands\ClosurableMakeCommand;
use Illuminate\Support\ServiceProvider;

class ClosurableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfigurationFiles();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/closurable.php' => config_path('closurable.php'),
        ], 'closurable');

        $this->bootCommands();
    }

    /**
     * Register the package config files.
     *
     * @return void
     */
    protected function registerConfigurationFiles(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/closurable.php', 'closurable');
    }

    /**
     * Register the commands for the package.
     *
     * @return void
     */
    protected function bootCommands(): void
    {
        $this->commands([
            Console\Commands\ClosurableMakeCommand::class
        ]);
    }
}
