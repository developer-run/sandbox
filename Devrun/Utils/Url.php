<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    Curl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Utils;


class Url
{

    /**
     * @param string|NULL $url
     *
     * @return bool
     */
    public static function urlExists(string $url = NULL): bool
    {
        if ($url == NULL) return false;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data     = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpcode >= 200 && $httpcode < 300;
    }


    /**
     * @param $url
     *
     * @return bool
     */
    public static function isValidDomain(string $url): bool
    {
        $validation = FALSE;

        // Parse URL
        $urlparts = parse_url(filter_var($url, FILTER_SANITIZE_URL));

        // Check host exist else path assign to host
        if (!isset($urlparts['host'])) {
            $urlparts['host'] = $urlparts['path'];
        }

        if ($urlparts['host'] != '') {
            // Add scheme if not found
            if (!isset($urlparts['scheme'])) {
                $urlparts['scheme'] = 'http';
            }
            // Validation
            if (checkdnsrr($urlparts['host'], 'A') && in_array($urlparts['scheme'], array('http', 'https')) && ip2long($urlparts['host']) === FALSE) {
                $urlparts['host'] = preg_replace('/^www\./', '', $urlparts['host']);
                $url              = $urlparts['scheme'] . '://' . $urlparts['host'] . "/";

                if (filter_var($url, FILTER_VALIDATE_URL) !== false && @get_headers($url)) {
                    $validation = TRUE;
                }
            }
        }

        return $validation;
    }


    /**
     * @param string $url
     *
     * @return string
     */
    public static function getDomainIP(string $url): string
    {
        return gethostbyname($url);
    }


}