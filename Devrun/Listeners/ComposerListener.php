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

    /** @var bool */
    private $write = true;


    /**
     * ComposerListener constructor.
     * @param bool $update
     * @param string $tags
     */
    public function __construct(bool $update, string $tags, bool $write)
    {
        $this->tags   = $tags;
        $this->update = $update;
        $this->write  = $write;
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

                if ($lastTimeHuman < $configLatTime) {

                    exec(trim("composer update {$this->tags}"), $output, $return);

                    if ($return == 0 && $this->write) {
                        $lastTimeHuman = date("Y-m-d H:i:s.u", filemtime($composerFile));

                        $moduleConfig[ModuleFacade::COMPOSER_HASH] = $lastTimeHuman;
                        $moduleFacade->saveModuleConfig($moduleConfig);
                    }
                }
            }
        }
    }


    /**
     * higher number of priority is better for previously start
     *
     * @return array
     */
    function getSubscribedEvents()
    {
        return [
            "Devrun\Module\ModuleFacade::onUpdate" => ['onUpdate', 20]
        ];
    }
}