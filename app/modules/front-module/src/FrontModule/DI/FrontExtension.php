<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2016
 *
 * @file    FrontExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\DI;

use Devrun\Config\CompilerExtension;
use Flame\Modules\Providers\IPresenterMappingProvider;
use Flame\Modules\Providers\IRouterProvider;
use FrontModule\Entities\TestSitesEntity;
use FrontModule\Entities\UserTestSiteEntity;
use FrontModule\Repositories\TestSiteRepository;
use FrontModule\Repositories\UserTestSiteRepository;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\DI\OrmExtension;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\ContainerBuilder;
use Nette\Environment;

class FrontExtension extends CompilerExtension implements IPresenterMappingProvider, IRouterProvider, IEntityProvider
{

    public $defaults = array(
    );


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);


        $builder->addDefinition($this->prefix('commonFilter'))
            ->setFactory('FrontModule\Filters\CommonFilter');


        /*
         * repositories
         */
        $builder->addDefinition($this->prefix('repository.userTestSite'))
            ->setType(UserTestSiteRepository::class)
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, UserTestSiteEntity::class);

        $builder->addDefinition($this->prefix('repository.testSite'))
            ->setType(TestSiteRepository::class)
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, TestSitesEntity::class);



        /*
         * presenters
         */



        /*
         * controls
         */
//        $builder->addDefinition($this->prefix('form.registrationFormFactory'))
//            ->setImplement('FrontModule\Forms\IRegistrationFormFactory')
//            ->setInject(true);

        $builder->addDefinition($this->prefix('form.secureFormFactory'))
            ->setImplement('FrontModule\Forms\ISecureFormFactory')
            ->setInject(true);


        $builder->addDefinition($this->prefix('control.environment'))
            ->setImplement('FrontModule\Control\IJSEnvironmentControl')
            ->setInject();




        // subscribers
//        $builder->addDefinition($this->prefix('listener.transactionListener'))
//            ->setFactory('FrontModule\Listeners\TransactionListener', [$builder->parameters['campaign']])
//            ->addTag(self::TAG_SUBSCRIBER);



    }


    /**
     * Returns array of ClassNameMask => PresenterNameMask
     *
     * @example return array('*' => 'Booking\*Module\Presenters\*Presenter');
     * @return array
     */
    public function getPresenterMapping()
    {
        return array(
            'Front' => 'FrontModule\*Module\Presenters\*Presenter',
        );
    }

    /**
     * Returns array of ServiceDefinition,
     * that will be appended to setup of router service
     *
     * @example https://github.com/nette/sandbox/blob/master/app/router/RouterFactory.php - createRouter()
     * @return \Nette\Application\IRouter
     */
    public function getRoutesDefinition()
    {
        $lang = Environment::getConfig('lang');

        $routeList     = new RouteList();

/*
        $routeList[]   = $quizRouter = new RouteList('Front');
        $quizRouter[] = new Route("[<locale={$lang} sk|hu|cs>/]<presenter>/<action>[/<id>]", array(
            'presenter' => array(
                Route::VALUE        => 'Homepage',
                Route::FILTER_TABLE => array(
                    'testovaci' => 'Test',
                ),
            ),
            'action'    => array(
                Route::VALUE        => 'default',
                Route::FILTER_TABLE => array(
                    'operace-ok' => 'operationSuccess',
                ),
            ),
            'id'        => null,
            'locale'    => [
                Route::FILTER_TABLE => [
                    'cz'  => 'cs',
                    'sk'  => 'sk',
                    'pl'  => 'pl',
                    'com' => 'en'
                ]]
        ));
*/

        /**
         * všeobecná routa
         */
        $routeList[] = new Route("[<locale={$lang} sk|hu|cs|en>/][<module=Front>/]<presenter>/<action>[/<id>]", array(
//            'module' => 'Front',
            'presenter' => array(
                Route::VALUE        => 'Homepage',
                Route::FILTER_TABLE => array(
                    'testovaci' => 'Test',
                ),
            ),
            'action'    => array(
                Route::VALUE        => 'default',
                Route::FILTER_TABLE => array(
                    'operace-ok' => 'operationSuccess',
                ),
            ),
            'id'        => null,
            'locale'    => [
                Route::FILTER_TABLE => [
                    'cz'  => 'cs',
                    'sk'  => 'sk',
                    'pl'  => 'pl',
                    'com' => 'en'
                ]]
        ));

        return $routeList;

    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return array(
            'FrontModule\Entities' => dirname(__DIR__) . '/Entities/',
        );
    }

}