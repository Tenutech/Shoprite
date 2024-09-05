<?php

namespace App\Providers;

use App\Services\ChatService;
use Illuminate\Support\ServiceProvider;

/**
 * ChatServiceProvider is responsible for binding the ChatService in the service container.
 * This allows for easy dependency injection and service resolution throughout the application.
 */
class ChatServiceProvider extends ServiceProvider
{
    /**
     * Register the ChatService as a singleton in the service container.
     *
     * When another part of the application asks for the ChatService, the same instance of the service
     * will be provided every time, ensuring consistent state and reducing overhead.
     *
     * @return void
     */
    public function register()
    {
        // Binding the ChatService as a singleton. This means that once the ChatService is instantiated,
        // the same instance will be reused on subsequent requests for the ChatService.
        $this->app->singleton(ChatService::class, function ($app) {
            return new ChatService();
        });
    }
}
