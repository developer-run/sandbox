<?php

/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    IInstaller.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module;

interface IInstaller
{

	/**
	 * @param IModule $module
	 */
	public function install(IModule $module);


	/**
	 * @param IModule $module
	 */
	public function uninstall(IModule $module);


	/**
	 * @param IModule $module
	 */
	public function upgrade(IModule $module, $from, $to);


	/**
	 * @param IModule $module
	 */
	public function downgrade(IModule $module, $from, $to);
}

