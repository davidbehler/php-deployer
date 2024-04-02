<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

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

        $filesystem = new Filesystem;

        $this->log('Ensure directory structure at '.$releasePath.' exists');

        try {
            $filesystem->mkdir($releasePath);
        } catch (\Exception $e) {
            $this->log('Could not create directory: '.$e->getMessage());

            return 1;
        }

        $this->log('Directory created');

        return 0;
    }
}