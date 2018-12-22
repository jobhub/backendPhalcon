<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces(
        [
            'App\Services' => realpath(__DIR__ . '/../services/'),
            'App\Controllers' => realpath(__DIR__ . '/../controllers/'),
            'App\Models' => realpath(__DIR__ . '/../models/'),
            'App\Middleware' => realpath(__DIR__ . '/../middleware/'),
            'App\Auth' => realpath(__DIR__ . '/../auth/'),
            'Dmkit\Phalcon' => realpath(__DIR__ . '/../../vendor/dmkit/phalcon-jwt-auth/src/Phalcon')
        ]
);
$loader->registerDirs(
        [
            $config->application->controllersDir,
            $config->application->modelsDir,
            $config->application->APIDir,
            $config->application->pluginsDir, 
            $config->application->libraryDir,
            $config->application->modelsResponsesDir,
        ]
)->register();
