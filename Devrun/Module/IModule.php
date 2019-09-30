<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    IModule.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module;


interface IModule
{

    public function getName();


    public function getDescription();


    public function getDefaultPageName();


    public function getKeywords();


    public function getLicense();


    public function getVersion();


    public function getAuthors();


    public function getAutoload();


    public function getRequire();


    public function getConfiguration();


    public function getExtra();


    public function getPath();


    public function getRelativePublicPath();


    public function getRelativePackagePath();


    public function getClassName();


    public function getInstallers();


    public function hasPublishedPages();


    public function hasPackagePages();

}