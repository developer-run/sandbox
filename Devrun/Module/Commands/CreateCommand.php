<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CreateCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\Module\ModuleFacade;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

    /** @var ModuleFacade */
    private $moduleManager;

    /** @var Container */
    private $container;

    /** @var string */
    protected $modulesDir;


    /**
     * CreateCommand constructor.
     *
     * @param Container    $container
     * @param ModuleFacade $moduleFacade
     */
    public function __construct(Container $container, ModuleFacade $moduleFacade)
    {
        parent::__construct();
        $this->container     = $container;
        $this->moduleManager = $moduleFacade;
        $this->modulesDir    = $container->parameters['modulesDir'];
    }


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('devrun:module:create')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->setDescription('Create module.');
    }


    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $modules = $this->moduleManager->getModules();
        $path = "{$this->modulesDir}/{$module}-module";

        if (isset($modules[$module])) {
            $output->writeln("<error>Module '{$module}' already exists.</error>");
            return;
        }

        if (file_exists($path)) {
            $output->writeln("<error>Path '" . $path . "' exists.</error>");
            return;
        }

        if (!is_writable(dirname($path))) {
            $output->writeln("<error>Path '" . dirname($path) . "' is not writable.</error>");
            return;
        }

        umask(0000);
        mkdir($path, 0777, TRUE);

        file_put_contents($path . '/Module.php', $this->getModuleFile($module));
        file_put_contents($path . '/composer.json', $this->getComposerFile($module));
        file_put_contents($path . '/readme.md', $this->getReadmeFile($module));

        umask(0000);
        mkdir($path . '/resources/config', 0777, TRUE);
        mkdir($path . '/resources/public', 0777, TRUE);
        mkdir($path . '/resources/translations', 0777, TRUE);
        mkdir($path . '/resources/layouts', 0777, TRUE);
        mkdir($path . '/src/' . $m = ucfirst($module) . 'Module', 0755, TRUE);
        mkdir($path . "/src/$m/Presenters", 0755, TRUE);
        mkdir($path . "/src/$m/DI", 0755, TRUE);
        mkdir($path . "/src/$m/Forms", 0755, TRUE);
        mkdir($path . "/src/$m/Entities", 0755, TRUE);
        mkdir($path . "/src/$m/Repositories", 0755, TRUE);
        mkdir($path . "/src/$m/Facades", 0755, TRUE);
        mkdir($path . "/src/$m/Presenters/templates", 0755, TRUE);

        // make module administration
        mkdir($path . "/src/Devrun/CmsModule/$m/Presenters/templates", 0755, TRUE);

        // make module extension
        file_put_contents($path . "/src/$m/DI/" . ucfirst($module) . 'Extension.php', $this->getDIFile($module));

        $this->moduleManager->create($module);


        $output->writeln(sprintf('<info>Module</info> <comment>%s</comment> <info>has been created in path</info> <comment>%s.</comment>', $module, $path));
        $output->writeln(sprintf('Add <comment>%s</comment> extension to your configuration', $module));
    }


    protected function getModuleFile($name)
    {
        return '<?php

namespace ' . ucfirst($name) . 'Module;

use Devrun\Module\ComposerModule;

class Module extends ComposerModule
{


}
';
    }


    protected function getComposerFile($name)
    {
        return '{
	"name":"devrun/' . $name . '-module",
	"description":"",
	"keywords":["cms", "nette", "devrun", "module"],
	"version":"0.4.0",
	"require":{
		"php":">=7.0"
	},
	"autoload":{
		"psr-0":{
			"' . ucfirst($name) . 'Module":""
		}
	},
	"extra":{
		"branch-alias":{
			"dev-master":"0.4.x-dev"
		}
	}
}
';
    }


    protected function getReadmeFile($name)
    {
        return '
' . ucfirst($name) . 'Module module for DevRun:CMS
======================================

Thank you for your interest.

Installation
------------

- Copy this folder to /vendor/devrun
- Active this module in administration
';
    }


    protected function getDIFile($name)
    {
        $ucName = ucfirst($name);

        return "<?php

namespace " . ucfirst($name) . "Module\\DI;

use Devrun\\Config\\CompilerExtension;
use Flame\\Modules\\Providers\\IPresenterMappingProvider;
use Kdyby\\Doctrine\\DI\\IEntityProvider;

class {$ucName}Extension extends CompilerExtension implements IPresenterMappingProvider, IEntityProvider
{
    public function loadConfiguration()
    {
        parent::loadConfiguration();".'
        
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);'."

    }


    public function getPresenterMapping()
    {
        return array(
            '$ucName' => '{$ucName}Module\*Module\Presenters\*Presenter',
        );
    }


    function getEntityMappings()
    {
        return array(
            '{$ucName}Module\Entities' => dirname(__DIR__) . '/Entities/',
        );
    }


}
";
    }



}