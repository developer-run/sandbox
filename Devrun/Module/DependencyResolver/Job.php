<?php

/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    Problem.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\DependencyResolver;

use Devrun\InvalidArgumentException;
use Nette\Object;
use Devrun\Module\IModule;


class Job extends Object
{

	const ACTION_INSTALL = 'install';

	const ACTION_UPGRADE = 'upgrade';

	const ACTION_UNINSTALL = 'uninstall';

	/** @var string */
	private $action;

	/** @var IModule */
	private $module;

	/** @var array */
	private static $actions = array(
		self::ACTION_INSTALL => TRUE,
		self::ACTION_UNINSTALL => TRUE,
		self::ACTION_UPGRADE => TRUE,
	);


	/**
	 * @param $action
	 * @param IModule $module
	 */
	public function __construct($action, IModule $module)
	{
		if (!isset(self::$actions[$action])) {
			throw new InvalidArgumentException("Action must be one of '" . join(', ', self::$actions) . "'. '{$action}' is given.");
		}

		$this->action = $action;
		$this->module = $module;
	}


	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * @return IModule
	 */
	public function getModule()
	{
		return $this->module;
	}
}

