<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

// @todo module loader is not complete yet
$configurator = new \Devrun\Config\Configurator(dirname(__DIR__) . '/app'/*, $loader*/);

$configurator->setDebugMode($remoteIP = '172.18.0.1'); // enable for your remote IP
//$configurator->setDebugMode(false);
//$configurator->enableDebugger();

if (\Devrun\Utils\Debugger::getIPAddress() == '37.221.241.103') {
    $configurator->setDebugMode(true);
}

$configurator->enableDebugger();
error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED
umask(0000);

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory(__DIR__)
//    ->addDirectory(__DIR__ . '/../vendor/devrun') // developer mode only
    ->ignoreDirs .= ', Qtests, test, resources';
$robotLoader->register();

$environment = (Nette\Configurator::detectDebugMode(['127.0.0.1', $remoteIP]) or (PHP_SAPI == 'cli' && Nette\Utils\Strings::startsWith(getHostByName(getHostName()), "127.0.")))
    ? 'development'
    : 'production';

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . "/config/config.$environment.neon");

if (($agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'admin') == 'admin') {
    $environment = 'admin';
    $configurator->addConfig(__DIR__ . "/config/config.$environment.neon");
}

$container = $configurator->createContainer();
Devrun\Doctrine\DoctrineForms\ToManyContainer::register();

return $container;
