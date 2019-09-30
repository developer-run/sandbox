<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    UnRegisterCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\InvalidArgumentException;
use Devrun\Module\ModuleFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnRegisterCommand extends Command
{
    /** @var ModuleFacade */
    private $moduleManager;


    /**
     * UnRegisterCommand constructor.
     *
     * @param ModuleFacade $moduleFacade
     */
    public function __construct(ModuleFacade $moduleFacade)
    {
        parent::__construct();
        $this->moduleManager = $moduleFacade;
    }



    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('devrun:module:unregister')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->setDescription('Unregister module.');
    }


    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->moduleManager->unregister($input->getArgument('module'));
            $output->writeln("Module '{$input->getArgument('module')}' has been unregistered.");

        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }



}