<?php
define('PROJECT_PATH', realpath(dirname(__FILE__)).'/');
define('RELEASES_PATH', PROJECT_PATH.'releases/');
define('LOGS_PATH', PROJECT_PATH.'logs/');

chdir(PROJECT_PATH);

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

date_default_timezone_set('UTC');

set_include_path(get_include_path().PATH_SEPARATOR.PROJECT_PATH);

set_time_limit(0);

require_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Finder\Finder;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use PhpDeployer\Command\DeployCommand;
use PhpDeployer\Logging\ErrorHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

$logFileLineFormatter = new LineFormatter(null, 'H:i:s', true);
$logFileHandler = new StreamHandler(LOGS_PATH.(new \DateTime('now'))->format('Y-m-d').'.log', Monolog\Level::Info);
$logFileHandler->setFormatter($logFileLineFormatter);

$logger = new Logger('DEPLOYER');
$logger->pushHandler($logFileHandler);

ErrorHandler::register($logger);

$dispatcher = new EventDispatcher;

$dispatcher->addListener(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event) use ($logger) {
    $logger->log(Monolog\Level::Critical, 'Deployment failed with exception: '.$event->getError()->getMessage());
});

$consoleApplication = new Application('PHP Deployer', '1.0');
$consoleApplication->setAutoExit(false);
$consoleApplication->setDispatcher($dispatcher);

$finder = new Finder();
$finder->files()->in(PROJECT_PATH.'src/Command')->name('*Command.php')->notName('BaseCommand.php');

if ($finder->hasResults()) {
    foreach ($finder as $file) {
        $className = 'PhpDeployer\Command\\'.$file->getFilenameWithoutExtension();

        if(class_exists($className)) {
            $consoleApplication->add(new $className(null, $logger));
        } else {
            $logger->log(Monolog\Level::Critical, 'Could not load command from path: '.$file->getRealPath());
        }
    }
}

$consoleApplication->setDefaultCommand((new DeployCommand(null, $logger))->getName());

$exitCode = $consoleApplication->run();

if ($exitCode > 255) {
    $exitCode = 255;
}

exit($exitCode);