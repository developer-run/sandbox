<?php
/**
 * This file is part of devrun
 * Copyright (c) 2019
 *
 * @file    bootstrapNetteTester.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

$tmp .= (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid());


var_dump($tmp);
die("ENS");