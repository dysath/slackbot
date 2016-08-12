<?php

namespace Seat\Slackbot;

use Illuminate\Support\ServiceProvider;
use Seat\Slackbot\Commands\SlackDaemon;
use Seat\Slackbot\Commands\SlackLogsClear;
use Seat\Slackbot\Commands\SlackUpdate;
use Seat\Slackbot\Commands\SlackChannelsUpdate;
use Seat\Slackbot\Commands\SlackUsersUpdate;

class SlackbotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCommands();
        $this->addRoutes();
        $this->addViews();
        $this->addPublications();
        $this->addTranslations();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/slackbot.config.php', 'slackbot.config');

        $this->mergeConfigFrom(
            __DIR__ . '/Config/slackbot.permissions.php', 'web.permissions');
        
        $this->mergeConfigFrom(
            __DIR__ . '/Config/package.sidebar.php', 'package.sidebar');
    }

    public function addCommands()
    {
        $this->commands([
            SlackUpdate::class,
            SlackChannelsUpdate::class,
            SlackUsersUpdate::class,
            SlackLogsClear::class,
            SlackDaemon::class
        ]);
    }
    
    public function addTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'slackbot');
    }
    
    public function addRoutes()
    {
        if (!$this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }
    
    public function addViews()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'slackbot');
    }
    
    public function addPublications()
    {
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ]);
    }
}
