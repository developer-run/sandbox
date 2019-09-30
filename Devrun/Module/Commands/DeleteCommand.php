<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    DeleteCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\Module\ModuleFacade;
use Devrun\Utils\FileTrait;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{

    use FileTrait;

    /** @var string */
    protected $modulesDir;

    /** @var string */
    protected $libsDir;


    /**
     * CreateCommand constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct();

        $this->libsDir    = $container->parameters['libsDir'];
        $this->modulesDir = $container->parameters['modulesDir'];
    }


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('devrun:module:delete')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->setDescription('Delete module.');
    }


    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        if (!is_dir($path = "{$this->libsDir}/devrun/{$module}-module") && !is_dir($path = "{$this->modulesDir}/{$module}-module")) {
            $output->writeln("<error>Path '" . $path . "' does not exist.</error>");
            return;
        }

        \Devrun\Utils\FileTrait::rmdir($path, TRUE);
        $output->writeln(sprintf('<info>Module</info> <comment>%s</comment> <info>has been deleted.</info>', $module));
    }


}