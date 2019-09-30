<?php
/**
 * This file is part of the vanocni_soutez
 * Copyright (c) 2014
 *
 * @file    Authorizator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Security;


use Devrun\CmsModule\Administration\Controls\PagePhotoControl;
use Devrun\CmsModule\Administration\Controls\SettingsControl;
use Devrun\CmsModule\Entities\PackageEntity;
use Devrun\CmsModule\Repositories\PackageRepository;
use FrontModule\Entities\MemberEntity;
use FrontModule\Entities\ProjectsEntity;
use FrontModule\Entities\TeamsYoungEntity;
use FrontModule\Managers\MemberManager;
use FrontModule\Repository\UserRepository;
use FrontModule\Security\Pages\HomePageAuthorizator;
use FrontModule\Security\Pages\MilestoneAuthorizator;
use FrontModule\Security\Pages\Project\ActionPageAuthorizator;
use FrontModule\Security\Pages\Project\AdvertisingPageAuthorizator;
use FrontModule\Security\Pages\Project\BlogPageAuthorizator;
use FrontModule\Security\Pages\Project\MessagePageAuthorizator;
use FrontModule\Security\Pages\ProjectAuthorizator;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;
use Nette\Security\User;
use ProjectModule\Forms\BudgetItemsForm;
use ProjectModule\Forms\EducationMembersForm;
use ProjectModule\Forms\MilestonesForm;
use ProjectModule\Forms\ProjectForm;
use ProjectModule\Forms\ProjectLightForm;
use ProjectModule\Forms\TeamYoungForm;
use Tracy\Debugger;

class Authorizator extends Permission
{
    /** @var ProjectsEntity */
    private $_project;

    /** @var User @inject */
    public $user;

    /** @var UserRepository */
    private $userRepository;

    /** @var MemberManager */
    public $memberManager;

    private $pages = [];


    /** @var EntityManager */
    private $entityManager;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        // roles
        $this->addRole('guest');
        $this->addRole('member', 'guest');
        $this->addRole('admin');
        $this->addRole('supervisor', 'admin');

        // resources
        $this->addResource('Front:Homepage');
        $this->addResource('Cms:Login');
        $this->addResource('Cms:Profile');
        $this->addResource('Cms:Dashboard');
        $this->addResource('Cms:Default');
        $this->addResource('Cms:Page');
        $this->addResource('Cms:Translate');
        $this->addResource('Cms:Images');
        $this->addResource('Cms:PageContent');
        $this->addResource('Cms:Module');
        $this->addResource('Cms:Log');
        $this->addResource('Cms:Domain');
        $this->addResource('Cms:Article:Default');
        $this->addResource('Cms:Article:Translate');
        $this->addResource('Cms:NavigationTreePage');
        $this->addResource('Cms:Catalog:Default');
        $this->addResource('Cms:Catalog:Category');
        $this->addResource('Cms:Catalog:Product');
        $this->addResource('Cms:Catalog:Orders');
        $this->addResource('Cms:Settings:Default');
        $this->addResource('Cms:Contest:Package');


        // custom resources
        $this->addResource('Cms:Front:Pexeso');
        $this->addResource('Cms:Pixstop:Result');
        $this->addResource('Cms:Puzzle:Result');
        $this->addResource('Cms:Calendar:Result');
        $this->addResource('Cms:Calendar:Settings');
        $this->addResource('Cms:Users');

        // forms resource
        $this->addResource('FrontModule\Forms\ContactTeamForm');
        $this->addResource('FrontModule\Forms\TeamYoungForm');
        $this->addResource('FrontModule\Forms\ProjectForm');
        $this->addResource($settingsControl = SettingsControl::class);
        $this->addResource($photoControl = PagePhotoControl::class);

        /*
         * privileges quest
         */
        $this->deny('guest', Permission::ALL);
        $this->allow('guest', 'Cms:Login', Permission::ALL);

        /*
         * privileges member
         */
        $this->deny('member', 'Cms:Login', Permission::ALL);
        $this->allow('member', 'Cms:Default', "default");
        $this->allow('member', 'Cms:Profile', Permission::ALL);
        $this->allow('member', 'Cms:Article:Translate', 'update');
        $this->allow('member', 'Cms:Article:Default', ['edit']);

        $this->allow('member', 'Cms:Contest:Package', Permission::ALL);
        $this->allow('member', 'Cms:Domain', Permission::ALL);
        $this->deny('member', 'Cms:Contest:Package', 'deleteDefault');


        $this->allow('member', 'Cms:Page', 'edit', function(IAuthorizator $autorizator, $role) {
//            Debugger::barDump($autorizator);
//            Debugger::barDump($role);


            return true;
        });



        /*
         * privileges admin
         */
        $this->allow('admin', Permission::ALL, Permission::ALL);
        $this->deny('admin', 'Cms:Login', Permission::ALL);

        /*
         * contest module test
         */
        $this->allow('admin', 'Cms:Page', ['edit', 'editAllPackages', 'editNotations']);
        $this->allow('admin', 'Cms:Article:Default', ['edit', 'resetArticles']);
        $this->allow('admin', 'Cms:Contest:Package', Permission::ALL);

        $this->deny('admin', 'Cms:Page', [ 'published', 'viewUnpublishedPages']);
        $this->deny('admin', 'Cms:Article:Default', 'editAllArticleAttributes');
        $this->deny('admin', 'Cms:Images', ['default',  'updateNamespace', 'removeNamespace!', 'delete!', 'viewTable!']);
        $this->deny('admin', 'Cms:Module');
        $this->deny('admin', 'Cms:Translate');
        $this->deny('admin', 'Cms:Log', Permission::ALL);
        $this->deny('admin', 'Cms:NavigationTreePage', Permission::ALL);
        $this->deny('admin', 'Cms:Contest:Package', 'deleteDefault');
        $this->deny('admin', 'Cms:Calendar:Settings', 'addDayQuestion');


        $this->allow('supervisor', 'Cms:Page', [ 'published', 'viewUnpublishedPages']);
        $this->allow('supervisor', 'Cms:Article:Default', ['edit', 'editAllArticles', 'editAllArticleAttributes']);
        $this->allow('supervisor', 'Cms:Images', ['default', 'updateNamespace', 'removeNamespace!', 'delete!', 'viewTable!']);
        $this->allow('supervisor', 'Cms:Module');
        $this->allow('supervisor', 'Cms:Users');
        $this->allow('supervisor', 'Cms:Translate');
        $this->allow('supervisor', 'Cms:Log', Permission::ALL);
        $this->allow('supervisor', 'Cms:NavigationTreePage', Permission::ALL);
        $this->allow('supervisor', 'Cms:Contest:Package', 'deleteDefault');
        $this->allow('supervisor', 'Cms:Calendar:Settings', 'addDayQuestion');

        $this->deny('admin', $settingsControl, ['editSettings']);
        $this->allow('supervisor', $settingsControl, ['editSettings']);

        $this->deny('admin', $photoControl, ['referenceImage']);
        $this->allow('supervisor', $photoControl, ['referenceImage']);






    }



    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * @param UserRepository $userRepository
     *
     * @return $this
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }


}

