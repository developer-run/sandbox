<?php


namespace Devrun\Module;

use DateTime;
use DateTimeZone;
use Exception;

class Version
{
    const MAJOR = 1;
    const MINOR = 2;
    const PATCH = 3;

    /**
     * @return string
     * @throws Exception
     */
    public static function get()
    {
        $commitInfo = trim(exec('git log --pretty=format:\'%h,%p,%ci\' --abbrev-commit'));


        $commitInfo = trim(exec('git log -1 --pretty=format:\'%h - %s (%ci)\' --abbrev-commit'));
        dump($commitInfo);

        $commitInfo = trim(exec('git describe --tags'));
        dump($commitInfo);

        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        $commitDate = new DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new DateTimeZone('UTC'));

        return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:i:s'));
    }
}
