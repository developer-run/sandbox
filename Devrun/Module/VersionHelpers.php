<?php

/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    VersionHelpers.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module;

class VersionHelpers
{

	/**
	 * @param $version
	 * @return array
	 */
	public static function normalizeRequire($version)
	{
		$ret = array();

		if (strpos($version, 'x') === FALSE) {
			if (substr($version, 1, 1) === '=') {
				$ret[] = array(substr($version, 0, 2) => substr($version, 2));
			} else {
				$ret[] = array(substr($version, 0, 1) => substr($version, 1));
			}
		} else {
			$ret[] = array('>=' => str_replace('x', '0', $version));
			$ret[] = array('<=' => str_replace('x', '999999', $version));
		}

		return $ret;
	}
}

