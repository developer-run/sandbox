<?php


namespace Devrun\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Events\DI\EventsExtension;
use Nette;
use Nette\PhpGenerator as Code;

/**
 * @author Pavel Paulik <pavel@paulik.seznam.cz>
 */
class FormsExtension extends Nette\DI\CompilerExtension implements IEntityProvider
{

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('entityFormMapper'))
            ->setClass('Devrun\Doctrine\DoctrineForms\EntityFormMapper');





//		$builder->addDefinition($this->prefix('controlFactory'))
//			->setClass('Kdyby\DoctrineForms\Builder\ControlFactory');
//
//		$builder->addDefinition($this->prefix('builderFactory'))
//			->setClass('Devrun\DoctrineForms\BuilderFactory');

        /*
         * Listeners
         */
        // user
        $builder->addDefinition($this->prefix('listener.blabeableListener'))
            ->setClass('Devrun\Doctrine\Listeners\BlameableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // time
        $builder->addDefinition($this->prefix('listener.timeStableListener'))
            ->setClass('Devrun\Doctrine\Listeners\TimeStableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // tree
        $builder->addDefinition($this->prefix('listener.treeListener'))
            ->setClass('Gedmo\Tree\TreeListener')
            ->addSetup('setAnnotationReader', ['@Doctrine\Common\Annotations\Reader'])
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // translatable
        $builder->addDefinition($this->prefix('listener.translatableListener'))
            ->setClass('Gedmo\Translatable\TranslatableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);


    }


    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('doctrineForms', new FormsExtension());
        };
    }

    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {

        return array(
//            'Gedmo\Translatable' => dirname($this->compiler->getConfig()['parameters']['appDir']) . "/vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity/",
            'Devrun\Doctrine' => dirname(__DIR__) . '/Doctrine/Entities/',
        );
    }
}

