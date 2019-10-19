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

    /** @var bool */
    private $update = false;

    /** @var string */
    private $tags = '';


    /**
     * ComposerListener constructor.
     * @param bool $update
     * @param string $tags
     */
    public function __construct(bool $update, string $tags)
    {
        $this->tags   = $tags;
        $this->update = $update;
    }


    /**
     * @todo update only hash check
     * update composer after modules update
     *
     * @param ModuleFacade $moduleFacade
     */
    public function onUpdate(ModuleFacade $moduleFacade)
    {
        if ($this->update) {
            shell_exec(trim("composer update {$this->tags}"));
        }

    }


    function getSubscribedEvents()
    {
        return [
            "Devrun\Module\ModuleFacade::onUpdate" => ['onUpdate', 10]
        ];
    }
}