<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    CoreExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DI;

use Devrun\Doctrine\Entities\UserEntity;
use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Doctrine\DI\OrmExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;

class CoreExtension extends CompilerExtension
{

    public $defaults = array(
        'cssUrlsFilterDir' => '%wwwDir%',
        'pageStorageExpiration' => '5 hours',
    );



    public function loadConfiguration()
    {
        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);

        // repositories
        $builder->addDefinition($this->prefix('repository.user'))
            ->setFactory('Devrun\Doctrine\Repositories\UserRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, UserEntity::class);


        // facades
        $builder->addDefinition($this->prefix('facade.user'))
            ->setFactory('Devrun\Facades\UserFacade');


        $builder->addDefinition($this->prefix('facade.module'))
            ->setFactory('Devrun\Module\ModuleFacade')
            ->addSetup('setPageStorageExpiration', [$config['pageStorageExpiration']]);


        // system
        $builder->addDefinition($this->prefix('authorizator'))
            ->setFactory('Devrun\Security\Authorizator');

        $builder->addDefinition($this->prefix('authenticator'))
            ->setFactory('Devrun\Security\Authenticator')
            ->setInject();

        $builder->addDefinition($this->prefix('listener.flush'))
            ->setFactory('Devrun\Listeners\FlushListener', [$builder->parameters['autoFlush']])
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        $builder->addDefinition($this->prefix('security.loggedUser'))
            ->setFactory('Devrun\Security\LoggedUser');




        // Commands
        $commands = array(
            // 'cache' => 'Devrun\Caching\Commands\Cache',
            'moduleUpdate' => 'Devrun\Module\Commands\Update',
            'moduleInstall' => 'Devrun\Module\Commands\Install',
            'moduleUninstall' => 'Devrun\Module\Commands\Uninstall',
            'moduleUpgrade' => 'Devrun\Module\Commands\Upgrade',
            'moduleRegister' => 'Devrun\Module\Commands\Register',
            'moduleUnregister' => 'Devrun\Module\Commands\UnRegister',
            'moduleList' => 'Devrun\Module\Commands\List',
            'moduleCreate' => 'Devrun\Module\Commands\Create',
            'moduleDelete' => 'Devrun\Module\Commands\Delete',
        );
        foreach ($commands as $name => $cmd) {
            $builder->addDefinition($this->prefix(lcfirst($name) . 'Command'))
                ->setFactory("{$cmd}Command")
                ->addTag(ConsoleExtension::TAG_COMMAND);
        }



    }

    public function beforeCompile()
    {
        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();

        // set module paths
        $moduleFacade = $builder->getDefinition($this->prefix('facade.module'));
        $moduleFacade->addSetup('setModulesPath', [$builder->parameters['modules']]);

        $this->checkDirStructure();
    }


    private function checkDirStructure()
    {
        $builder = $this->getContainerBuilder();
        $systemPaths=[
            $builder->parameters['wwwCacheDir'],
        ];

        foreach ($systemPaths as $systemPath) {
            if (!is_dir($systemPath)) {
                try {
                    mkdir($systemPath, 0777, true);

                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            }
        }

    }


}