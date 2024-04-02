<?php
namespace PhpDeployer\Command;

use PhpDeployer\Service\ReleaseManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class CleanupReleaseCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:cleanup-release';

    protected function configure(): void
    {
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $filesystem = new Filesystem;

        $filesystem->remove($releasePath);

        $this->log('Release cleanup completed');

        return 0;
    }
}