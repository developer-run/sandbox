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
use Nette\SmartObject;

class Problem
{

    use SmartObject;

	/** @var Job[] */
	protected $solutions = array();


	/**
	 * @param Job $job
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addSolution(Job $job)
	{
		if ($this->hasSolution($job)) {
			throw new InvalidArgumentException("Solution '{$job->getModule()->getName()}:{$job->getAction()}' is already added.");
		}

		$this->solutions[$job->getModule()->getName()] = $job;
	}


    /**
     * @param Job $job
     *
     * @return bool
     */
	public function hasSolution(Job $job)
	{
		return isset($this->solutions[$job->getModule()->getName()]);
	}


	/**
	 * @return Job[]
	 */
	public function getSolutions()
	{
		return array_merge($this->solutions);
	}
}

