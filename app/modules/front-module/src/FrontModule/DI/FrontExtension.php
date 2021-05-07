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
use FrontModule\Filters\CommonFilter;
use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\ContainerBuilder;

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
            ->setType(CommonFilter::class);


        /*
         * repositories
         */



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


        $builder->addDefinition($this->prefix('form.registrationFormFactory'))
            ->setImplement('FrontModule\Forms\IRegistrationFormFactory')
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
        global /** @var \Nette\DI\Container $container */
        $container;

        $lang = $container->getParameters()['lang'];

        $routeList = new RouteList();

        $routeList[]   = $frontRouter = new RouteList('Front');
        $frontRouter[] = new Route("[<locale={$lang} sk|hu|cs>/]<presenter>/<action>[/<id>]", array(
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