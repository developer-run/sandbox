<?php
/**
 * This file is part of the nivea-2017-03-care
 * Copyright (c) 2017
 *
 * @file    CommonFilter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Filters;

use Nette\Http\IRequest;
use Nette\Http\Session;
use Nette\Utils\Strings;
use Tracy\Debugger;

class CommonFilter
{

    const SESSION_SECTION = 'questions';

    /** @var IRequest */
    private $httpRequest;


    /** @var Session */
    private $session;


    /**
     * CommonFilter constructor.
     *
     * @param IRequest $httpRequest
     */
    public function __construct(IRequest $httpRequest, Session $session)
    {
        $this->httpRequest = $httpRequest;
        $this->session = $session;
    }


    /**
     * @param $number
     *
     * @return string two digits number [08]
     */
    public static function numberTwoDigits($number)
    {
        return is_numeric($number)
            ? sprintf("%02d", $number)
            : $number;
    }


}