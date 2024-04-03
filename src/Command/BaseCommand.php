<?php
namespace PhpDeployer\Command;

use PhpDeployer\Service\ReleaseManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    protected LoggerInterface $logger;
    protected ReleaseManager $releaseManager;

    public function __construct(string $name = null, LoggerInterface $logger, ReleaseManager $releaseManager)
    {
        $this->logger = $logger;
        $this->releaseManager = $releaseManager;

        parent::__construct($name);
    }

    protected function log($message, $context = null)
    {
        if(!$context) {
            $context = [];
        }

        $context['command'] = self::getDefaultName();

        $this->logger->log(\Monolog\Level::Info, $message, $context);
    }
}