<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    Strings.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Utils;


class Strings
{

    public static function starts_with_upper($str) {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    public static function starts_with_lower($str) {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtoupper($chr, "UTF-8") != $chr;
    }

}