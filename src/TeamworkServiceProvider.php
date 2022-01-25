<?php

namespace Mpociot\Teamwork;

use Illuminate\Support\ServiceProvider;
use Mpociot\Teamwork\Middleware\TeamOwner;

class TeamworkServiceProvider extends ServiceProvider
{
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
     * Publish Teamwork configuration.
     */
    protected function publishConfig()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('teamwork.php'),
        ]);
    }

    /**
     * Publish Teamwork migration.
     */
    protected function publishMigration()
    {
        if (! class_exists('TeamworkSetupTables')) {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../database/migrations/2016_05_18_000000_teamwork_setup_tables.php' => database_path('migrations/'.$timestamp.'_teamwork_setup_tables.php'),
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
        $this->app['router']->aliasMiddleware('teamowner', TeamOwner::class);
        $this->mergeConfig();
        $this->registerTeamwork();
        $this->registerCommands();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerTeamwork()
    {
        $this->app->alias(Teamwork::class, 'teamwork');
    }

    /**
     * Merges user's and teamwork's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'teamwork'
        );
    }

    /**
     * Register scaffolding command.
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
