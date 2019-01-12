<?php
// SETUP THE CONFIG
$authConfig = [
    'public' => [
        '^public/'
    ],
    'access_control' => [
        ['path' => '^/admin', 'role' => 'ROLE_ADMIN'],
        ['path' => '^/private', 'role' => 'ROLE_USER'],
        ['path' => '^/guest', 'role' => 'ROLE_GUEST']
    ],
    'roles_inheritance' => [
        'ROLE_ADMIN' => ['ROLE_USER', 'ROLE_GUEST']
    ]
];

return $authConfig;
