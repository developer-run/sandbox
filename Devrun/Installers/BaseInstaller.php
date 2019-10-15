<?php

/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    BaseInstaller.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Installers;

use Devrun;
use Devrun\Module\IInstaller;
use Devrun\Module\IModule;
use Devrun\Utils\FileTrait;
use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Nette\Utils\Validators;

/**
 * Class BaseInstaller
 * @package Devrun\Module\Installers
 *
 */
class BaseInstaller implements IInstaller
{

    use SmartObject;
    use FileTrait;

	/** @var array */
	protected $actions = array();

	/** @var string */
	protected $resourcesDir;

	/** @var string */
	protected $configDir;

	/** @var string */
	protected $baseDir;

    /** @var array */
	protected $parameters = [];

	/**
	 * @param \Nette\DI\Container $context
	 */
	public function __construct(Container $context)
	{
		$this->resourcesDir = $context->parameters['resourcesDir'];
		$this->parameters = $context->parameters;
		$this->configDir = $context->parameters['configDir'];
		$this->baseDir = $context->parameters['baseDir'];
	}


    /**
     * @param IModule $module
     *
     * @throws \Exception
     */
	public function install(IModule $module)
	{
		try {
            $name          = $module->getName();
            $configuration = $module->getConfiguration();

            /*
             * update module packages.json
             * @todo not tested yet
             */
//            $packagesDir = new \SplFileInfo($module->getPath() . $module->getRelativePackagePath());
//            $packagesDir = $packagesDir->getRealPath();
//
//            if (file_exists($packageFile = $this->baseDir . "/package.json"   )) {
//
//                // is package.json valid?
//                if ($appPackages   = json_decode(file_get_contents($packageFile), true)) {
//                    $appModuleInfo = isset($appPackages['modules']) ? $appPackages['modules'] : [];
//
//                    /** @var \SplFileInfo $file */
//                    foreach (Finder::findFiles('*.json')->in($packagesDir) as $key => $file) {
//                        $packName = $file->getBasename('.json');
//
//                        // is module json valid?
//                        if ($unExpandJson = json_decode(file_get_contents($file->getRealPath()), true)) {
//                            $expandJson   = Devrun\DI\Helpers::expand($unExpandJson, $this->parameters, true);
//
//                            $moduleJson[$packName]  = $expandJson;
//                            $appPackages['modules'] = Devrun\Utils\Arrays::array_replace_recursive_ex($appModuleInfo, $moduleJson);
//                        }
//                    }
//
//                    $string = json_encode($appPackages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
//                    file_put_contents($packageFile, $string);
//                }
//            }


            /*
             * create resources dir
             */
			$resourcesDir = $this->resourcesDir;
			$moduleDir = $resourcesDir . "/{$name}Module";
			$targetDir = new \SplFileInfo($module->getPath() . $module->getRelativePublicPath());
			$targetDir = $targetDir->getRealPath();

            if (!file_exists($moduleDir) && file_exists($targetDir)) {
				umask(0000);
				@mkdir(dirname($moduleDir), 0777, TRUE);
				if (!@symlink(FileTrait::getRelativePath(dirname($moduleDir), $targetDir), $moduleDir) && !file_exists($moduleDir)) {
					FileTrait::copy($targetDir, $moduleDir);
				}

				$this->actions[] = function () use ($resourcesDir) {
					if (is_link($resourcesDir)) {
						unlink($resourcesDir);
					} else {
						FileTrait::rmdir($resourcesDir, TRUE);
					}
				};
			}

            /*
             * update main config.neon
             */
			if (count($configuration) > 0) {
				$orig = $data = $this->loadConfig();

				// $data = array_merge_recursive($data, $configuration);
                $data = Devrun\Utils\Arrays::array_replace_recursive_ex($data, $configuration); // better array_merge_recursive

				$this->saveConfig($data);
				$this->actions[] = function (BaseInstaller $self) use ($orig) {
					$self->saveConfig($orig);
				};
			}

		} catch (\Exception $e) {
			$actions = array_reverse($this->actions);

			try {
				foreach ($actions as $action) {
					$action($this);
				}
			} catch (\Exception $ex) {
				echo $ex->getMessage();
			}

			throw $e;
		}
	}


    /**
     * @param IModule $module
     */
	public function uninstall(IModule $module)
	{
		$name = $module->getName();
		$configuration = $module->getConfiguration();

		// update main config.neon
		if (count($configuration) > 0) {
			$orig = $data = $this->loadConfig();
			$data = $this->getRecursiveDiff($data, $configuration);

			// remove extension parameters
			$configuration = $module->getConfiguration();
			if (isset($configuration['extensions'])) {
				foreach ($configuration['extensions'] as $key => $values) {
					if (isset($data[$key])) {
						unset($data[$key]);
					}
				}
			}

			$this->saveConfig($data);

			$this->actions[] = function (BaseInstaller $self) use ($orig) {
				$self->saveConfig($orig);
			};
		}

		// remove resources dir
		$resourcesDir = $this->resourcesDir . "/{$name}Module";
		if (file_exists($resourcesDir)) {
			if (is_link($resourcesDir)) {
				unlink($resourcesDir);
			} else {
				FileTrait::rmdir($resourcesDir, TRUE);
			}
		}
	}


    /**
     * @param IModule $module
     * @param         $from
     * @param         $to
     */
	public function upgrade(IModule $module, $from, $to)
	{
	}


    /**
     * @param IModule $module
     * @param         $from
     * @param         $to
     */
	public function downgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param array $arr1
	 * @param array $arr2
	 * @return array
	 */
	protected function getRecursiveDiff($arr1, $arr2)
	{
		$isList = Validators::isList($arr1);
		$arr2IsList = Validators::isList($arr2);

		foreach ($arr1 as $key => $item) {
			if (!is_array($arr1[$key])) {

				// if key is numeric, remove the same value
				if (is_numeric($key) && ($pos = array_search($arr1[$key], $arr2)) !== FALSE) {
					unset($arr1[$key]);
				} //

				// else remove the same key
				else if ((!$isList && isset($arr2[$key])) || ($isList && $arr2IsList && array_search($item, $arr2) !== FALSE)) {
					unset($arr1[$key]);
				} //

			} elseif (isset($arr2[$key])) {
				$arr1[$key] = $item = $this->getRecursiveDiff($arr1[$key], $arr2[$key]);

				if (is_array($item) && count($item) === 0) {
					unset($arr1[$key]);
				}
			}
		}

		if ($isList) {
			$arr1 = array_merge($arr1);
		}

		return $arr1;
	}


	/**
	 * @return string
	 */
	protected function getConfigPath()
	{
		return $this->configDir . '/config.neon';
	}


	/**
	 * @return array
	 */
	protected function loadConfig()
	{
		$config = new NeonAdapter();
		return $config->load($this->getConfigPath());
	}


	/**
	 * @param $data
	 */
	public function saveConfig($data)
	{
		$config = new NeonAdapter();
		file_put_contents($this->getConfigPath(), $config->dump($data));
	}
}

