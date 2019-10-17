<?php

namespace Devrun\Listeners;

use Devrun\Module\ModuleFacade;
use Kdyby\Events\Subscriber;

/**
 * Class ComposerListener
 * @package Devrun\Listeners
 */
class ComposerListener implements Subscriber
{


    public function onUpdate(ModuleFacade $moduleFacade)
    {
        $result = shell_exec('composer update --no-interaction --ansi');
    }



    function getSubscribedEvents()
    {
        return [
            "Devrun\Module\ModuleFacade::onUpdate" => ['onUpdate', 10]
        ];
    }
}