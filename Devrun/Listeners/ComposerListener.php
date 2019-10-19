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
     * update composer after modules update
     *
     * @param ModuleFacade $moduleFacade
     */
    public function onUpdate(ModuleFacade $moduleFacade)
    {
        if ($this->update) {
            $baseDir = $moduleFacade->getContext()->getParameters()['baseDir'];

            if (file_exists($composerFile = $baseDir . "/composer.lock")) {
                $lastTimeHuman = date("Y-m-d H:i:s.u", filemtime($composerFile));

                $moduleConfig  = $moduleFacade->loadModuleConfig();
                $configLatTime = $moduleConfig[ModuleFacade::COMPOSER_HASH] ?? '';

                if ($lastTimeHuman != $configLatTime) {

                    shell_exec(trim("composer update {$this->tags}"));
                    $lastTimeHuman = date("Y-m-d H:i:s.u", filemtime($composerFile));

                    $moduleConfig[ModuleFacade::COMPOSER_HASH] = $lastTimeHuman;
                    $moduleFacade->saveModuleConfig($moduleConfig);
                }
            }
        }

    }


    function getSubscribedEvents()
    {
        return [
            "Devrun\Module\ModuleFacade::onUpdate" => ['onUpdate', 10]
        ];
    }
}