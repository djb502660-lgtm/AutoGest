<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    App\Modules\Chatbot\ChatbotServiceProvider::class,
    RepositoryServiceProvider::class,
];
