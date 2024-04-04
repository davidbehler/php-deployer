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
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));
        $sharedPath = PROJECT_PATH.'shared/';

        $filesystem = new Filesystem;

        if(!$filesystem->exists($sharedPath)) {
            $this->log('Ensure shared directory exists');

            try {
                $filesystem->mkdir($sharedPath);
            } catch (\Exception $e) {
                $this->log('Could not create directory: '.$e->getMessage());

                return 1;
            }

            $this->log('Shared directory created');
        }


        $this->log('Ensure directory structure at '.$releasePath.' exists');

        try {
            $filesystem->mkdir($releasePath);
        } catch (\Exception $e) {
            $this->log('Could not create directory: '.$e->getMessage());

            return 1;
        }

        $this->log('Release directory structure created');

        return 0;
    }
}