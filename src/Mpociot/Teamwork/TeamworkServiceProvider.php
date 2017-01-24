<?php namespace Mpociot\Teamwork;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\ServiceProvider;

class TeamworkServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishMigration();
    }

    /**
     * Publish Teamwork configuration
     */
    protected function publishConfig()
    {
        // Publish config files
        $this->publishes( [
            __DIR__ . '/../../config/config.php' => config_path( 'teamwork.php' ),
        ] );
    }

    /**
     * Publish Teamwork migration
     */
    protected function publishMigration()
    {
        if (! class_exists('TeamworkSetupTables')) {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../../database/migrations/2016_05_18_000000_teamwork_setup_tables.php' => database_path('migrations/'.$timestamp.'_teamwork_setup_tables.php'),
              ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerTeamwork();
        $this->registerFacade();
        $this->registerCommands();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerTeamwork()
    {
        $this->app->bind('teamwork', function($app) {
            return new Teamwork($app);
        });
    }

    /**
     * Register the vault facade without the user having to add it to the app.php file.
     *
     * @return void
     */
    public function registerFacade() {
        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Teamwork', 'Mpociot\Teamwork\Facades\Teamwork');
        });
    }
    /**
     * Merges user's and teamwork's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', 'teamwork'
        );
    }

    /**
     * Register scaffolding command
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MakeTeamwork::class,
            ]);
        }
    }
}
