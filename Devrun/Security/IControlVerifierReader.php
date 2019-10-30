<?php

/**
 * This file is part of the Devrun:Framework
 *
 * Copyright (c) 2019
 *
 * @file    IControlVerifierReader.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 *
 */

namespace Devrun\Security;


interface IControlVerifierReader
{

    /**
     * @param $class
     * @return array
     */
    public function getSchema($class);
}
