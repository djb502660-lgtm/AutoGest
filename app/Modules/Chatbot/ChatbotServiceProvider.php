<?php

namespace App\Modules\Chatbot;

use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        if (file_exists($routes = __DIR__.'/routes.php')) {
            $this->loadRoutesFrom($routes);
        }

        // Load views from the module folder.
        $viewsPath = __DIR__.'/Resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'chatbot');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Nothing to register for now.
    }
}
