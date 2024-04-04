<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class EnsureProperOwnerCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:ensure-proper-owner';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
        $this->addOption('deploymentOwner', null, InputOption::VALUE_REQUIRED, 'Release owner', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));

        $deploymentOwner = $input->getOption('deploymentOwner');

        $filesystem = new Filesystem;

        $this->log('Ensure proper owner for '.$releasePath.' is '.$deploymentOwner);

        try {
            $filesystem->chown($releasePath, $deploymentOwner, true);
        } catch (\Exception $e) {
            $this->log('Could not change owner: '.$e->getMessage());

            return 1;
        }

        $this->log('Owner set');

        return 0;
    }
}