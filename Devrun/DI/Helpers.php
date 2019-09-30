<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    Helpers.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DI;


class Helpers extends \Nette\DI\Helpers
{


    /**
     * extended Nette expand to key array
     *
     * @param       $var
     * @param array $params
     * @param bool  $recursive
     *
     * @return array|mixed
     */
    public static function expand($var, array $params, $recursive = FALSE)
    {
        if (is_array($var)) {
            $res = [];
            foreach ($var as $key => $val) {
                $exKey = parent::expand($key, $params, $recursive);
                $res[$exKey] = self::expand($val, $params, $recursive);
            }
            return $res;
        }

        return parent::expand($var, $params, $recursive);
    }


}