<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeploymentStatusCommand extends BaseCommand
{
    protected static $defaultName = 'deployment:status';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->releaseManager->getConfig());

        return 0;
    }
}