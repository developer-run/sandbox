<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$configurator = new \Devrun\Config\Configurator(dirname(__DIR__) . '/tests', $debugMode = true, $loader);

//error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED

$environment = $configurator->isDebugMode()
    ? 'development'
    : 'production';

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory(dirname(__DIR__) . "/Devrun")
    ->addDirectory(dirname(__DIR__) . "/Gedmo")
    ->ignoreDirs .= ', templates, test, resources';
$robotLoader->register();

$environment = 'test';

$configurator->addConfig(__DIR__ . '/sandbox/config/config.neon');
$configurator->addConfig(__DIR__ . "/sandbox/config/config.$environment.neon");

$container = $configurator->createContainer();
return $container;
