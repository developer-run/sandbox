<?php


namespace Devrun\Listeners;

use Devrun\Migrations\Migration;
use Devrun\Module\ModuleFacade;
use Kdyby\Events\Subscriber;

/**
 * Class MigrationListener
 * @package Devrun\Listeners
 */
class MigrationListener implements Subscriber
{


    /**
     * @param ModuleFacade $moduleFacade
     */
    public function onUpdate(ModuleFacade $moduleFacade)
    {
        Migration::continue($moduleFacade->getContext());

    }


    function getSubscribedEvents()
    {
        return [
            "Devrun\Module\ModuleFacade::onUpdate" => ['onUpdate', 20]
        ];
    }


}