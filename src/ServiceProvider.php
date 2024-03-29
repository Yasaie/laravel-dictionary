<?php

namespace Yasaie\Dictionary;

use Illuminate\Console\Scheduling\Schedule;
use Yasaie\Dictionary\Commands\RemoveDuplicateDictionaries;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!class_exists('CreateDictionariesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_dictionaries_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_dictionaries_table.php'),
            ], 'migrations');
        }

        $this->commands(RemoveDuplicateDictionaries::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('dictionary:remove-duplicate')
                ->hourly();
        });
    }
}
