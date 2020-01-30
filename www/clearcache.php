<?php

opcache_reset();
require __DIR__ . '/../vendor/autoload.php';
\Devrun\Utils\FileTrait::purge($dir = dirname(__DIR__) . "/temp/cache");

echo "<span style='color: darkmagenta; font-weight: bold;'>'$dir'</span> is purged";
