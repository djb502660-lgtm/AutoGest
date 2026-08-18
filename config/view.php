<?php

return [

    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        // Vercel usa un filesystem de solo lectura en el deploy (/var/task).
        // Por eso, los compiled views deben ir a /tmp.
        '/tmp/framework/views'
    ),

];
