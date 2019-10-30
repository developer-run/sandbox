<?php

/**
 * This file is part of the Devrun:Framework
 *
 * Copyright (c) 2019
 *
 * @file    IControlVerifier.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 *
 */

namespace Devrun\Security;

interface IControlVerifier
{

	/**
	 * @param $element
	 * @return bool
	 */
	public function checkRequirements($element);


	/**
	 * @param IControlVerifierReader $reader
	 */
	public function setControlVerifierReader(IControlVerifierReader $reader);


	/**
	 * @return IControlVerifierReader
	 */
	public function getControlVerifierReader();
}
