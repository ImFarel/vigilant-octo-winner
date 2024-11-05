<?php

namespace App\Providers;

use App\Repositories\ChatroomRepository;
use App\Repositories\MessageRepository;
use App\Services\ChatroomService;
use App\Services\MessageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(ChatroomService::class, function ($app) {
            return new ChatroomService(new ChatroomRepository());
        });

        $this->app->singleton(MessageService::class, function ($app) {
            return new MessageService(new MessageRepository());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
