<?php
namespace PhpDeployer\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    private LoggerInterface $logger;

    public function __construct(string $name = null, LoggerInterface $logger)
    {
        $this->logger = $logger;

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