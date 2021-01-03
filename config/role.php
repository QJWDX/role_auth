<?php

return [
    'user' => 'App\Models\Base\User',
    'user_table' => 'users',
    'except' => [
        'class' => [],
        'action' => [],
        'combine' => []
    ],
    'subscribers' => [
//        "Runone\Role\Listeners\LoginSubscriber"
    ],
    'login_success_event' => [
//        'Runone\Role\Events\Login'
    ],
    'login_fail_event' => []
];