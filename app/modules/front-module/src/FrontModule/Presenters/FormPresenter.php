<?php
/**
 * This file is part of the nivea-2017-11-advent_kalendar
 * Copyright (c) 2017
 *
 * @file    CalendarPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;

use Devrun\Doctrine\Entities\UserEntity;
use Devrun\Doctrine\Repositories\UserRepository;
use FrontModule\Control\ITimeControlFactory;
use FrontModule\Entities\TestSitesEntity;
use FrontModule\Entities\UserTestSiteEntity;
use FrontModule\Forms\IRegistrationFormFactory;
use FrontModule\Forms\RegistrationFormFactory;
use FrontModule\Repositories\TestSiteRepository;
use FrontModule\Repositories\UserTestSiteRepository;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class FormPresenter extends BaseAppPresenter
{

    /** @var IRegistrationFormFactory @inject */
    public $registrationFormFactory;

    /** @var ITimeControlFactory @inject */
    public $timeControlFactory;

    /** @var UserTestSiteRepository @inject */
    public $userTestSiteRepository;

    /** @var TestSiteRepository @inject */
    public $testSiteRepository;

    /** @var UserRepository @inject */
    public $userRepository;


    /** @var UserTestSiteEntity */
    private $userTestSiteEntity;

    /** @var UserEntity */
    private $userEntity;



    public function actionDefault()
    {
        $section = $this->getSection('play');

        $uid = isset($section->uid) ? $section->uid : null;

        if ($uid) {
            if ($this->userEntity = $this->userRepository->findOneBy(['username' => $uid, 'active' => true])) {
                if (!$this->userTestSiteEntity = $this->userTestSiteRepository->findOneBy(['user.username' => $uid])) {
                    $this->userTestSiteEntity = new UserTestSiteEntity($this->userEntity);
                    $this->userTestSiteEntity->setCreatedBy($this->userEntity);
                }
            }
        }

        if (!$this->userEntity) {
//            $this->template->redirect = "Homepage:";
            $this->ajaxRedirect('Homepage:');
        }

        if (!$this->userTestSiteEntity) {
            $this->ajaxRedirect('Homepage:');
        }

        $this->template->userTestSiteEntity = $this->userTestSiteEntity;
    }



    /**
     * @param $name
     *
     * @return \FrontModule\Forms\RegistrationFormFactory
     */
    protected function createComponentRegistrationForm($name)
    {
        $form = $this->registrationFormFactory->create();
        $form
            ->setTranslator($this->translator->domain('site.' . $name))
            ->create();

        if (!$entity = $this->userTestSiteEntity) {
            $entity = new UserTestSiteEntity();
        }

        $form->bindEntity($entity);

        $form->bootstrap3Render();
        $form->getElementPrototype()->addAttributes([
            'class' => 'form-horizontal _ajax',
        ]);
        $form->onSuccess[] = function (RegistrationFormFactory $form, $values) {

            $section = $this->getSection('play');
            $section->send = true;

            /** @var UserTestSiteEntity $entity */
            $entity = $form->getEntity();

            $entity->getUser()
                ->setActive(false)
                ->setActiveDateTime(new DateTime())
                ->setRole(UserEntity::ROLE_GUEST);

            $entity->setCreatedBy($entity->getUser());
            $entity->getUser()->privacy = $values->privacy;

            $this->userTestSiteRepository->getEntityManager()->persist($entity)->flush();
            $this->ajaxRedirect("ThankYou:");
        };

        return $form;
    }


    /**
     * @return \FrontModule\Control\TimeControl
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function createComponentTimeControl()
    {
        $control = $this->timeControlFactory->create();
        $control->setRedirectLink($this->link("Homepage:"));

        $section = $this->getSection('play');
        $time = isset($section->time) ? $section->time : new DateTime();

        $control->setTime($time);

        return $control;
    }


}