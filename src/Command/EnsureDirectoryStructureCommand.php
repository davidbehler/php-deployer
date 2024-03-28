<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnsureDirectoryStructureCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:ensure-directory-structure';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'ensure directory structure';

        return 0;
    }
}