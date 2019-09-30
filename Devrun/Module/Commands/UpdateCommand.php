<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    UpdateCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\InvalidArgumentException;
use Devrun\Module\ModuleFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{

    /** @var ModuleFacade */
    private $moduleManager;

    /**
     * ListCommand constructor.
     *
     * @param ModuleFacade $moduleFacade
     */
    public function __construct(ModuleFacade $moduleFacade)
    {
        parent::__construct();
        $this->moduleManager = $moduleFacade;
    }

    protected function configure()
    {
        $this
            ->setName('devrun:module:update')
            ->setDescription('Update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->moduleManager->update();
            $output->writeln('Modules have been updated.');
        } catch (InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }


}