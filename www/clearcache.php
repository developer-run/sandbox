<?php

opcache_reset();
require __DIR__ . '/../vendor/autoload.php';

$dir = dirname(__DIR__) . "/temp/cache";
echo "<span style='color: greenyellow; font-weight: bold;'>'$dir'</span> is purge";

\Devrun\Utils\FileTrait::purge($dir);
echo "<span style='color: darkmagenta; font-weight: bold;'>'$dir'</span> is purged";
