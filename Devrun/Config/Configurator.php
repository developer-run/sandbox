<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    Configurator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Config;

use Composer\Autoload\ClassLoader;
use Devrun;
use Nette\DI;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Loaders\RobotLoader;

/**
 * Class Configurator
 *
 * @package Devrun\Config
 */
class Configurator extends \Nette\Configurator
{

    /** @var string|array */
    protected $sandbox;

    /** @var Container */
    protected $container;

    /** @var RobotLoader */
    protected $robotLoader;

    /** @var Compiler */
    protected $compiler;

    /** @var ClassLoader */
    protected $classLoader;


    /**
     * @param string       $sandbox
     * @param string|array $debugMode
     * @param ClassLoader  $classLoader
     */
    public function __construct($sandbox, $debugMode = NULL, ClassLoader $classLoader = NULL)
    {
        parent::__construct();
        $this->sandbox     = $sandbox;
        $this->classLoader = $classLoader;

        try {
            umask(0000);
            $this->parameters = array_merge($this->parameters, $this->getSandboxParameters());
            $this->parameters = array_merge($this->parameters, $this->getDevrunDefaultParameters($this->parameters));

            $this->validateConfiguration();
            $this->loadModulesConfiguration();

            if ($debugMode) $this->setDebugMode($debugMode);

            $this->enableDebugger($this->parameters['logDir']);
            $this->setTempDirectory($this->parameters['tempDir']);

            if ($this->classLoader) {
                $this->registerModuleLoaders();
            }
        } catch (InvalidArgumentException $e) {
            die($e->getMessage());
        }

    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function getSandboxParameters()
    {
        $mandatoryParameters = array('wwwDir', 'appDir', 'baseDir', 'libsDir', 'logDir', 'dataDir', 'tempDir', 'logDir', 'configDir', 'wwwCacheDir', 'publicDir', 'resourcesDir', 'modulesDir', 'migrationsDir');

        if (!is_string($this->sandbox) && !is_array($this->sandbox)) {
            throw new InvalidArgumentException("SandboxDir must be string or array, " . gettype($this->sandbox) . " given.");
        }

        if (is_string($this->sandbox)) {
            if (!file_exists($file = $this->sandbox . '/sandbox.php')) {
                throw new InvalidArgumentException('Sandbox must contain sandbox.php file with path configurations.');
            }
            $parameters = require $file;
        } else {
            $parameters = $this->sandbox;
        }

        foreach ($mandatoryParameters as $item) {
            if (!isset($parameters[$item])) {
                throw new InvalidArgumentException("Sandbox parameters does not contain '{$item}' parameter.");
            }
        }

        foreach ($parameters as $name => $parameter) {
            if (!is_dir($parameter)) {
                throw new InvalidArgumentException("Sandbox parameter '$name' directory does not exist '{$parameter}'");
            }
        }

        return $parameters;
    }


    protected function getDevrunDefaultParameters($parameters = NULL)
    {
        $parameters = (array)$parameters;

        $debugMode = isset($parameters['debugMode']) ? $parameters['debugMode'] : static::detectDebugMode();
        $ret = array(
            'debugMode' => $debugMode,
            'environment' => ($e = static::detectEnvironment()) ? $e : ($debugMode ? 'development' : 'production'),
            'container' => array(
                'class' => 'SystemContainer',
                'parent' => 'Nette\DI\Container',
            )
        );

        if (!file_exists($settingsFile = $parameters['configDir'] . '/settings.php')) {
            throw new InvalidArgumentException("file $settingsFile not found");
        }

        $settings = require $settingsFile;

        foreach ($settings['modules'] as &$module) {
            $module['path'] = \Nette\DI\Helpers::expand($module['path'], $parameters);
        }
        $parameters = $settings + $parameters + $ret;
        $parameters['productionMode'] = !$parameters['debugMode'];

        return $parameters;
    }


    public static function detectEnvironment()
    {
        return isset($_SERVER['SERVER_NAME'])
            ? $_SERVER['SERVER_NAME']
            : (function_exists('gethostname') ? gethostname() : NULL);
    }

    public function setEnvironment($name)
    {
        $this->parameters['environment'] = $name;
        return $this;
    }


    /**
     * @return Container
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }


    /**
     * @return Container
     */
    public function createContainer()
    {
        // create container
        $container = parent::createContainer();

        // register robotLoader and configurator
        if ($this->robotLoader) {
            $container->addService('robotLoader', $this->robotLoader);
        }
        $container->addService('configurator', $this);

        return $container;
    }





    protected function validateConfiguration()
    {
        $mandatoryConfigs = array('settings.php', 'config.neon');

        foreach ($mandatoryConfigs as $config) {
            if (!file_exists($this->parameters['configDir'] . '/' . $config)) {
                if (file_exists($origFile = $this->parameters['configDir'] . '/' . $config . '.orig')) {
                    if (is_writable($this->parameters['configDir']) && file_exists($origFile)) {
                        copy($origFile, $this->parameters['configDir'] . '/' . $config);
                    } else {
                        throw new InvalidArgumentException("Config directory is not writable.");
                    }
                } else {
                    throw new InvalidArgumentException("Configuration file '{$config}' does not exist.");
                }
            }
        }
    }


    /**
     * load default module config
     */
    protected function loadModulesConfiguration()
    {
        if (isset($this->parameters['modules'])) {
            foreach ($this->parameters['modules'] as $items) {
                if (file_exists($fileConfig = $items['path'] . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.neon")) {
                    $this->addConfig($fileConfig);
                }
            }
        }
    }


    protected function registerModuleLoaders()
    {
        if (isset($this->parameters['modules'])) {
            foreach ($this->parameters['modules'] as $items) {
                if (isset($items['autoload']['psr-0'])) {
                    foreach ($items['autoload']['psr-0'] as $key => $val) {
                        $this->classLoader->add($key, $items['path'] . '/' . $val);
                    }
                }
                if (isset($items['autoload']['files'])) {
                    foreach ($items['autoload']['files'] as $file) {
                        include_once $items['path'] . '/' . $file;
                    }
                }
            }
        }
    }

    public function generateContainer(DI\Compiler $compiler)
    {
        $this->onCompile[] = function (Configurator $config, Compiler $compiler) {
            $compiler->addExtension('events', new \Kdyby\Events\DI\EventsExtension());
            $compiler->addExtension('console', new \Kdyby\Console\DI\ConsoleExtension());
            $compiler->addExtension('annotations', new \Kdyby\Annotations\DI\AnnotationsExtension());
            $compiler->addExtension('doctrine', new \Kdyby\Doctrine\DI\OrmExtension());
            $compiler->addExtension('translation', new \Kdyby\Translation\DI\TranslationExtension());
            $compiler->addExtension('translatable', new \Zenify\DoctrineBehaviors\DI\TranslatableExtension());
            $compiler->addExtension('migrations', new \Nextras\Migrations\Bridges\NetteDI\MigrationsExtension());
            $compiler->addExtension('monolog', new \Kdyby\Monolog\DI\MonologExtension());
            $compiler->addExtension('modules', new \Flame\Modules\DI\ModulesExtension());

            $compiler->addExtension('core', new Devrun\DI\CoreExtension());
            $compiler->addExtension('imageStorage', new \Devrun\DI\ImagesExtension());
            $compiler->addExtension('doctrineForms', new Devrun\DI\FormsExtension());
        };

        return parent::generateContainer($compiler);
    }


}