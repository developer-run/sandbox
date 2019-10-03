<?php

$configurator = new \Devrun\Config\Configurator(dirname(__DIR__) . '/tests' /*, $loader*/);
$configurator->setDebugMode(false);

//error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory(__DIR__)
//    ->addDirectory(__DIR__ . '/../vendor/devrun') // developer mode only
    ->ignoreDirs .= ', Qtests, test, resources';
$robotLoader->register();


//$configurator->setDebugMode(true);
//$configurator->enableTracy($log);
//$configurator->setTempDirectory($tmp);


$environment = 'test';

$configurator->addConfig(__DIR__ . '/sandbox/config/config.neon');
$configurator->addConfig(__DIR__ . "/sandbox/config/config.$environment.neon");

$container = $configurator->createContainer();

return $container;
