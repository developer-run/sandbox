<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$configurator = new \Devrun\Config\Configurator(dirname(__DIR__) . '/tests', true, $loader);

//error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory(__DIR__)
    ->ignoreDirs .= ', templates, test, resources';
$robotLoader->register();

$environment = 'test';

$configurator->addConfig(__DIR__ . '/sandbox/config/config.neon');
$configurator->addConfig(__DIR__ . "/sandbox/config/config.$environment.neon");

$container = $configurator->createContainer();
return $container;
