<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class EnsureDirectoryStructureCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:ensure-directory-structure';

    protected function configure(): void
    {
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $this->log('Ensure '.$releasePath.' exists');

        return 0;
    }
}