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
use FrontModule\Entities\UserTestSiteEntity;
use FrontModule\Forms\IRegistrationFormFactory;
use FrontModule\Forms\RegistrationFormFactory;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class FormPresenter extends BaseAppPresenter
{

    /** @var IRegistrationFormFactory @inject */
    public $registrationFormFactory;



    public function actionDefault()
    {
        $section = $this->getSection('play');

        $uid = isset($section->uid) ? $section->uid : null;

        if (!$uid) {
            $this->ajaxRedirect('Homepage:');
        }

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
            ->setTestSites(['Site 1', 'Pardubice', 'Kolín', 'Rokycany'])
            ->create();

        $form->bootstrap3Render();
        $form->getElementPrototype()->addAttributes([
            'class' => 'form-horizontal _ajax',
        ]);
        $form->onSuccess[] = function (RegistrationFormFactory $form, $values) {

            $section = $this->getSection('play');
            $section->send = true;
            $section->values = $values;

//            /** @var UserTestSiteEntity $entity */
//            $entity = $form->getEntity();
//
//            $entity->getUser()
//                ->setActive(false)
//                ->setActiveDateTime(new DateTime())
//                ->setRole(UserEntity::ROLE_GUEST);
//
//            $entity->setCreatedBy($entity->getUser());
//            $entity->getUser()->privacy = $values->privacy;
//
//            $this->userTestSiteRepository->getEntityManager()->persist($entity)->flush();
            $this->ajaxRedirect("ThankYou:");
        };

        return $form;
    }




}