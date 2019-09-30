<?php
/**
 * This file is part of the nova.superletuska.cz
 * Copyright (c) 2016
 *
 * @file    CssUrlFilter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\WebLoader\Filters;

use Nette\Http\IRequest;

/**
 * Class CssUrlFilter
 */
class CssUrlsFilter extends \WebLoader\Filter\CssUrlsFilter
{

    /**
     * CssUrlFilter constructor.
     *
     * @param string   $docRoot
     * @param IRequest $httpRequest
     */
    public function __construct($docRoot, IRequest $httpRequest)
    {
        parent::__construct($docRoot, $httpRequest->getUrl()->getBasePath());
    }


}
