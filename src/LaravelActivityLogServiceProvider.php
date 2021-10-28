<?php

namespace Mindz\LaravelActivityLog;

use Illuminate\Support\ServiceProvider;

class LaravelActivityLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreateActivityLogTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_activity_log_table.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_activity_log_table.php'),
                ], 'migrations');
            }
        }
    }
}
