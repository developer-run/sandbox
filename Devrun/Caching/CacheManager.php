<?php

/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    Configurator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Caching;

use Devrun\Utils\FileTrait;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Finder;


class CacheManager extends Object
{
    use FileTrait;

	/** @var Cache */
	protected $cache;

	/** @var string */
	protected $cacheDir;

	/** @var string */
	protected $sessionsDir;


	/**
	 * @param IStorage $fileStorage
	 * @param $cacheDir
	 * @param $sessionsDir
	 */
	public function __construct(IStorage $fileStorage, $cacheDir, $sessionsDir)
	{
		$this->cache = new Cache($fileStorage);
		$this->cacheDir = $cacheDir;
		$this->sessionsDir = $sessionsDir;
	}


	public function clean()
	{
		foreach (Finder::find('*')->in($this->cacheDir) as $file) {
			$path = $file->getPathname();

			if (is_dir($path)) {
				FileTrait::rmdir($path, TRUE);
			} else {
				unlink($path);
			}
		}
	}


	/**
	 * @param $namespace
	 * @throws \Nette\InvalidArgumentException
	 */
	public function cleanNamespace($namespace)
	{
		$dir = $this->getDirFromNamespace($namespace);

		if (!file_exists($dir)) {
			throw new InvalidArgumentException("Namespace '{$namespace}' does not exist.");
		}

		FileTrait::rmdir($this->getDirFromNamespace($namespace), TRUE);
	}


	public function cleanSessions()
	{
		foreach (Finder::find('*')->in($this->sessionsDir) as $file) {
			$path = $file->getPathname();

			if (is_dir($path)) {
				FileTrait::rmdir($path, TRUE);
			} else {
				unlink($path);
			}
		}
	}


	/**
	 * @param $namespace
	 * @return string
	 */
	protected function getDirFromNamespace($namespace)
	{
		return $this->cacheDir . '/_' . $namespace;
	}
}

