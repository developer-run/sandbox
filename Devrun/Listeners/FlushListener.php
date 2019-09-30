<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    FlushListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Listeners;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;

class FlushListener implements Subscriber
{

    /** @var EntityManager */
    private $entityManager;

    private $autoFlush;

    /**
     * FlushListener constructor.
     *
     * @param bool          $autoFlush
     * @param EntityManager $entityManager
     */
    public function __construct(bool $autoFlush, EntityManager $entityManager)
    {
        $this->autoFlush     = $autoFlush;
        $this->entityManager = $entityManager;
    }


    /**
     * @param IPresenter             $presenter
     * @param IResponse|TextResponse $response
     */
    public function onShutdown(IPresenter $presenter, IResponse $response)
    {
        if (!$this->autoFlush || $this->checkChangeSet()) {
            return;
        }

        if ($response instanceof TextResponse) {
            $html = (string)$response->getSource();
            if ($this->checkChangeSet()) {
                return;
            }
        }
    }


    private function checkChangeSet()
    {
        $uow = $this->entityManager->getUnitOfWork();
        if ($uow->getScheduledEntityInsertions() || $uow->getScheduledEntityUpdates() || $uow->getScheduledEntityDeletions()) {
            $this->entityManager->flush();
            return true;
        }

        return false;
    }


    function getSubscribedEvents()
    {
        return [
            'Nette\Application\UI\Presenter::onShutdown'
        ];

    }
}