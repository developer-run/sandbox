<?php

/**
 * run once
 */
//require __DIR__ . '/../../../vendor/autoload.php';
//require_once __DIR__ . '/../../app/modules/cms-module/src/Security/DummyUserStorage.php';


//$configurator = new Nette\Configurator;
//$configurator->setDebugMode(FALSE);
//$configurator->setDebugMode(TRUE);

use Devrun\Utils\EscapeColors;

$configurator = new \Devrun\Config\Configurator($appDir = dirname(__DIR__) . '/../../../../app'/*, $loader*/);

$sandboxParameters = $configurator->getSandboxParameters();

if (!is_dir($log = $sandboxParameters['logDir'] . '/tests/')) {
    mkdir($log);
}
//$configurator->enableDebugger($log);

if (!is_dir($tmp = $sandboxParameters['tempDir'] . '/tests/')) {
    mkdir($tmp);
}

//Tester\Helpers::purge($tmp);

$tmp .= (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid());
echo(EscapeColors::fg_color("blue", PHP_EOL . "temp) $tmp" . PHP_EOL));

//var_dump($tmp);

//Tester\Helpers::purge($tmp);

define(TEMP_DIR, $tmp);
if (!is_dir($tmp)) {
    mkdir($tmp);
}
if (!is_dir($tmp . "/cache")) {
    mkdir($tmp . "/cache");
}

// every delete fake test session
$debugDirs = [
//    'latte',
//    '_routes',
//    '_Air.Action',
];
foreach ($debugDirs as $debugDir) {
    $deleteDir = $tmp . "/cache/$debugDir";
    if (is_dir($deleteDir)) {
        Tester\Helpers::purge($deleteDir);
    }
}


$configurator->setDebugMode(true);
$configurator->enableTracy($log);
$configurator->setTempDirectory($tmp);



//$configurator->enableDebugger();
error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED

$robotLoader = $configurator->createRobotLoader();
$robotLoader
    ->addDirectory($appDir)
    ->addDirectory(__DIR__)
//    ->addDirectory(__DIR__ . '/../../../../../vendor/devrun') // developer mode only
    ->ignoreDirs .= ', tests, test, resources';
$robotLoader->register();

$environment = 'test';

$configurator->addConfig($appDir . '/config/config.neon');
$configurator->addConfig($appDir . "/config/config.$environment.neon");

if (($agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'admin') == 'admin') {
    $environment = 'admin';
    $configurator->addConfig($appDir . "/config/config.$environment.neon");
}

$container = $configurator->createContainer();
$container->getService('application')->errorPresenter = 'Front:Error';

Devrun\Doctrine\DoctrineForms\ToManyContainer::register();



//$container->removeService('nette.userStorage');
//$container->addService('nette.userStorage', new \CmsModule\Security\DummyUserStorage());


//TestInit::initDatabase($container);
//TestInit::initMigrations($container);


$_container = $container;

return $container;


class TestInit
{
    private static $loadDump = true;

    static $initialized = false;



    private static function migration(\Nette\DI\Container $container, $conn)
    {
        $dbal = new \Nextras\Migrations\Bridges\DoctrineDbal\DoctrineAdapter($conn);
        $driver = new \Nextras\Migrations\Drivers\MySqlDriver($dbal);
        $controller = new \Devrun\Migrations\Controllers\ExecController($driver);


        $appDir = $container->parameters['appDir'];
        $baseDir = $appDir . "/../migrations";

        $controller->addGroup('structures', "$baseDir/structures");
        $controller->addGroup('basic-data', "$baseDir/basic-data", array('structures'));
        $controller->addGroup('dummy-data', "$baseDir/dummy-data", array('basic-data'));
        $controller->addGroup('production', "$baseDir/production", array('basic-data'));
        $controller->addExtension('sql', new \Nextras\Migrations\Extensions\SqlHandler($driver));
        $controller->addExtension('php', new \Nextras\Migrations\Extensions\PhpHandler(['container' => $container]));

        $controller->run($action = 'run', $groups = ['structures', 'basic-data', 'dummy-data'], \Nextras\Migrations\Engine\Runner::MODE_RESET);
    }


    public static function initMigrations(\Nette\DI\Container $container)
    {
        /** @var \Kdyby\Doctrine\EntityManager $em */
        $em = $container->getByType('Kdyby\Doctrine\EntityManager');
        $conn = $em->getConnection();

        if (file_exists($dbSnapshot = TEMP_DIR . "/db-snapshot.sql")) {
            echo(EscapeColors::fg_color("cyan", PHP_EOL . "init database from snapshot..." . PHP_EOL));
            \Kdyby\Doctrine\Helpers::loadFromFile($conn, $dbSnapshot);

        } else {
            self::migration($container, $conn);

            $username = $container->parameters['database']['user'];
            $password = $container->parameters['database']['password'];
            $dbname = $container->parameters['database']['dbname'];

            echo(EscapeColors::fg_color("cyan", PHP_EOL . "make dump of generated migration..." . PHP_EOL));

            $command = "mysqldump -u $username -p$password $dbname > $dbSnapshot";
            exec($command);
        }


    }

    /**
     * @param \Nette\DI\Container $container
     * @deprecated use initMigrations instead
     */
    public static function initDatabase(\Nette\DI\Container $container)
    {
        /** @var \Kdyby\Doctrine\EntityManager $em */
        $em   = $container->getByType('Kdyby\Doctrine\EntityManager');
        $conn = $em->getConnection();
//        dump($appDir = $container->getParameters()['appDir']);

        $conn->prepare("SET FOREIGN_KEY_CHECKS = 0")->execute();
        //$conn->prepare("TRUNCATE TABLE users")->execute();
//        $conn->prepare("TRUNCATE TABLE pexeso_settings_cards")->execute();
//        $conn->prepare("TRUNCATE TABLE projects_versions")->execute();
        //$conn->prepare("TRUNCATE TABLE members")->execute();
        //$conn->prepare("TRUNCATE TABLE teammembers")->execute();
//        $conn->prepare("TRUNCATE TABLE emails")->execute();
//        $conn->prepare("TRUNCATE TABLE log")->execute();
        $conn->prepare("SET FOREIGN_KEY_CHECKS = 1")->execute();

        if (file_exists($dumpSql = __DIR__ . '/dump.sql')) {
            if (self::$loadDump) {
                \Kdyby\Doctrine\Helpers::loadFromFile($conn, $dumpSql);

            } else {
//                EscapeColors::all_bg();
//                EscapeColors::all_fg();
                echo(EscapeColors::bg_color("magenta", PHP_EOL . strtoupper("--- load `dump.sql` is off ---") . PHP_EOL . PHP_EOL));
            }

        }



    }
}

