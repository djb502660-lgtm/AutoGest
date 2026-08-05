<?php

use App\Modules\Chatbot\ChatbotServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    ChatbotServiceProvider::class,
    RepositoryServiceProvider::class,
];
