<?php
// SETUP THE CONFIG
$authConfig = [
    'secretKey' => '923753F2317FC1EE5B52DF23951B1',
    'payload' => [
            'exp' => 1440,
            'iss' => 'phalcon-jwt-auth'
        ],
     'ignoreUri' => [   
        ]
];

return $authConfig;
