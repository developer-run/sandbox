<?php

return array(
    'appDir'        => __DIR__,
    'configDir'     => __DIR__ . '/config',
    'dataDir'       => __DIR__ . '/data',
    'modulesDir'    => __DIR__ . '/modules',
    'baseDir'       => dirname(__DIR__),
    'logDir'        => dirname(__DIR__) . '/log',
    'tempDir'       => dirname(__DIR__) . '/temp',
    'libsDir'       => dirname(__DIR__) . '/vendor', // lze zadat aktuální umístění devrunu
    'wwwDir'        => dirname(__DIR__) . '/www',
    'imageDir'      => dirname(__DIR__) . '/images',
    'wwwCacheDir'   => dirname(__DIR__) . '/webTemp',
    'publicDir'     => dirname(__DIR__) . '/media',
    'resourcesDir'  => dirname(__DIR__) . '/resources',
    'migrationsDir' => dirname(__DIR__) . '/migrations',
);
