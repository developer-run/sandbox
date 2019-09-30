<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    PresenterUtil.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Utils;


use Nette\Utils\Strings;

class PresenterUtil
{
    const PUBLIC_PRESENTER_TAG = 'public.presenter';


    /**
     * FrontModule\Presenters\HomepagePresenter => front.presenters.homepage
     *
     * @param $presenterClassName
     *
     * @return string
     */
    public static function getServiceNameFromClassName($presenterClassName) {
        return strtolower(Strings::replace($presenterClassName, ['/Module/' => null, '/Presenter$/' => null, '%\\\%' => '.']));
    }


}