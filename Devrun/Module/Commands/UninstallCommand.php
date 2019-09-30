<?php

/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    InstallCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Commands;

use Devrun\Module\DependencyResolver\Job;
use Devrun\Module\DependencyResolver\Problem;
use Devrun\Module\IModule;
use Devrun\Module\ModuleFacade;
use Nette\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;


class UninstallCommand extends Command
{

	/** @var ModuleFacade */
	protected $moduleManager;


    /**
     * UninstallCommand constructor.
     *
     * @param ModuleFacade $moduleManager
     */
	public function __construct(ModuleFacade $moduleManager)
	{
		parent::__construct();
		$this->moduleManager = $moduleManager;
	}


	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this
			->setName('devrun:module:uninstall')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->addOption('noconfirm', NULL, InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Uninstall module.');
	}


	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var $module IModule */
		$module = $this->moduleManager->createInstance($input->getArgument('module'));

		try {
			/** @var $problem Problem */
			$problem = $this->moduleManager->testUninstall($module);
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
			return;
		}

		if (!$input->getOption('noconfirm') && count($problem->getSolutions()) > 0) {
			foreach ($problem->getSolutions() as $job) {
				$output->writeln("<info>{$job->getAction()} : {$job->getModule()->getName()}</info>");
			}
			$output->writeln("<info>uninstall : {$module->getName()}</info>");

            $dialog = $this->getHelperSet()->get('question');
            $question = new ConfirmationQuestion('<question>Continue with this actions? [y/N]</question> ', false);
            if (!$dialog->ask($input, $output, $question)) {
                return;
            }
		}

        try {
			foreach ($problem->getSolutions() as $job) {
				$this->moduleManager->doAction($job->getAction(), $job->getModule());

				if ($job->getAction() === Job::ACTION_INSTALL){
					$output->writeln("Module '{$job->getModule()->getName()}' has been installed.");
				} else if($job->getAction() === Job::ACTION_UNINSTALL){
					$output->writeln("Module '{$job->getModule()->getName()}' has been uninstalled.");
				} else if($job->getAction() === Job::ACTION_UPGRADE){
					$output->writeln("Module '{$job->getModule()->getName()}' has been upgraded.");
				}
			}
			$this->moduleManager->uninstall($module);
			$output->writeln("Module '{$input->getArgument('module')}' has been uninstalled.");
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
		}
	}
}
