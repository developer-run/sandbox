<?php
/**
 * This file is part of the smart-up
 * Copyright (c) 2016
 *
 * @file    IAuthorizator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Security;

interface IAuthorizator
{
    const
        TAG_USER_PERMISSION = 'security.userPermission';


    /**
     * set permissions allow
     *
     * @return void
     */
    public function setPermissions();


}