<?php

namespace Devrun\DI;

use Nette;

/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class ImagesExtension extends Nette\DI\CompilerExtension
{

    public $defaults = array(
        'storageDir' => "%wwwDir%". '/public',
        'assetsDir' => "%wwwDir%". '/public',

        'data_path'          => '%wwwDir%/../public/data',
        'data_dir'           => 'data',
        'algorithm_file'     => 'sha1_file',
        'algorithm_content'  => 'sha1',
        'quality'            => 85,
        'default_transform'  => 'fit',
        'noimage_identifier' => 'noimage/03/no-image.png',
        'friendly_url'       => FALSE

    );




	public function loadConfiguration()
	{
		$config  = $this->getConfig($this->defaults);

        /** @var Nette\DI\ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();

		$engine = $builder->getDefinition('nette.latteFactory');
		$install = 'Devrun\Application\UI\Images\Macros\Latte::install';

		if (method_exists('Latte\Engine', 'getCompiler')) {
			$engine->addSetup('Devrun\Application\UI\Images\Macros\Latte::install(?->getCompiler())', array('@self'));
		} else {
			$engine->addSetup($install . '(?->compiler)', array('@self'));
		}

        $builder->addDefinition($this->prefix('storage'))
            ->setFactory('Devrun\Storage\ImageStorage')
            ->setArguments([
                $builder->parameters['wwwDir'],
                $config['data_path'],
                $config['data_dir'],
                $config['algorithm_file'],
                $config['algorithm_content'],
                $config['quality'],
                $config['default_transform'],
                $config['noimage_identifier'],
                $config['friendly_url']
            ]);

	}


}
