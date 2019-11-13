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
use Devrun\Listeners\ComposerListener;
use Devrun\Listeners\MigrationListener;
use Devrun\Security\ControlVerifierReaders\AnnotationReader;
use Devrun\Security\ControlVerifiers\ControlVerifier;
use Exception as ExceptionAlias;
use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Doctrine\DI\OrmExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;

class CoreExtension extends CompilerExtension
{

    public $defaults = array(
        'cssUrlsFilterDir'      => '%wwwDir%',
        'pageStorageExpiration' => '5 hours',
        'update'                => [
            'composer'      => false,
            'composerWrite' => false,
            'composerTags'  => '--no-interaction --ansi',
            'migration'     => false,
        ],
    );


    public function loadConfiguration()
    {
        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);

        // repositories
        $builder->addDefinition($this->prefix('repository.user'))
                ->setType('Devrun\Doctrine\Repositories\UserRepository')
                ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, UserEntity::class);


        // facades
        $builder->addDefinition($this->prefix('facade.user'))
                ->setType('Devrun\Facades\UserFacade');


        $builder->addDefinition($this->prefix('facade.module'))
                ->setType('Devrun\Module\ModuleFacade')
                ->addSetup('setPageStorageExpiration', [$config['pageStorageExpiration']]);


        // system
        $builder->addDefinition($this->prefix('controlVerifier'))
                  ->setType(ControlVerifier::class);

        $builder->addDefinition($this->prefix('controlVerifierReader'))
                  ->setType(AnnotationReader::class);

        $builder->getDefinition('user')
                ->setFactory('Devrun\Security\User');

//        $builder->addDefinition($this->prefix('authorizator'))
//                ->setType('Devrun\Security\Authorizator');

//        $builder->addDefinition($this->prefix('authenticator'))
//                ->setType('Devrun\Security\Authenticator')
//                ->setInject();

        $builder->addDefinition($this->prefix('listener.flush'))
                ->setFactory('Devrun\Listeners\FlushListener', [$builder->parameters['autoFlush']])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        $builder->addDefinition($this->prefix('security.loggedUser'))
                ->setType('Devrun\Security\LoggedUser');

        // http
        $builder->getDefinition('httpResponse')
                  ->addSetup('setHeader', array('X-Powered-By', 'Nette Framework && Devrun:Framework'));

        // Commands
        $commands = array(
            // 'cache' => 'Devrun\Caching\Commands\Cache',
            'moduleUpdate'     => 'Devrun\Module\Commands\Update',
            'moduleInstall'    => 'Devrun\Module\Commands\Install',
            'moduleUninstall'  => 'Devrun\Module\Commands\Uninstall',
            'moduleUpgrade'    => 'Devrun\Module\Commands\Upgrade',
            'moduleRegister'   => 'Devrun\Module\Commands\Register',
            'moduleUnregister' => 'Devrun\Module\Commands\UnRegister',
            'moduleList'       => 'Devrun\Module\Commands\List',
            'moduleCreate'     => 'Devrun\Module\Commands\Create',
            'moduleDelete'     => 'Devrun\Module\Commands\Delete',
        );
        foreach ($commands as $name => $cmd) {
            $builder->addDefinition($this->prefix(lcfirst($name) . 'Command'))
                    ->setFactory("{$cmd}Command")
                    ->addTag(ConsoleExtension::TAG_COMMAND);
        }


        // Subscribers
        $builder->addDefinition($this->prefix('subscriber.composer'))
                ->setFactory(ComposerListener::class, [$config['update']['composer'], $config['update']['composerTags'], $config['update']['composerWrite']])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        $builder->addDefinition($this->prefix('subscriber.migration'))
                ->setFactory(MigrationListener::class, [$config['update']['migration']])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);


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
        $builder     = $this->getContainerBuilder();
        $systemPaths = [
            $builder->parameters['wwwCacheDir'],
        ];

        foreach ($systemPaths as $systemPath) {
            if (!is_dir($systemPath)) {
                try {
                    mkdir($systemPath, 0777, true);

                } catch (ExceptionAlias $e) {
                    die($e->getMessage());
                }
            }
        }

    }


}