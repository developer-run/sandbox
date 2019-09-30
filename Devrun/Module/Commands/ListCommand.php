<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ListCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\InvalidArgumentException;
use Devrun\Module\ModuleFacade;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{

    /** @var ModuleFacade */
    private $moduleManager;

    /** @var Container */
    private $container;

    /**
     * ListCommand constructor.
     *
     * @param Container    $container
     * @param ModuleFacade $moduleFacade
     */
    public function __construct(Container $container, ModuleFacade $moduleFacade)
    {
        parent::__construct();
        $this->container     = $container;
        $this->moduleManager = $moduleFacade;
    }

    protected function configure()
    {
        $this
            ->setName('devrun:module:list')
            ->setDescription('List modules.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            foreach ($this->moduleManager->findModules() as $module) {

                $configVersion = isset($this->container->parameters['modules'][$module->getName()][ModuleFacade::MODULE_VERSION])
                    ? $this->container->parameters['modules'][$module->getName()][ModuleFacade::MODULE_VERSION]
                    : "`unknown`";

                if ($configVersion == $module->getVersion()) {
                    $version = $module->getVersion();
                } else {
                    $version = $module->getVersion() . ' (needs upgrade from: ' . $configVersion . ')';
                }

                $output->writeln(sprintf('<info>%25s</info> | status: <comment>%-12s</comment> | version: <comment>%s</comment>', $module->getName(), $this->moduleManager->getStatus($module), $version));
            }
        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }


}