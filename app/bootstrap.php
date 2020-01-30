<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$configurator = new \Devrun\Config\Configurator(dirname(__DIR__) . '/app', $debugMode = null, $loader);

error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory(__DIR__)
    ->ignoreDirs .= ', templates, test, resources';
$robotLoader->register();

$container = $configurator->createContainer();
//Devrun\Doctrine\DoctrineForms\ToManyContainer::register();

return $container;
