<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class CloneRepositoryCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:clone-repository';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
        $this->addOption('deploymentUser', null, InputOption::VALUE_REQUIRED, 'Deployment user', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deploymentUser = $input->getOption('deploymentUser');
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));

        $this->log('Clone repository into '.$releasePath);

        $commandToRun = 'sudo -u '.$deploymentUser.' -H git clone --depth 1 '.$_ENV['REPOSITORY'].' '.escapeshellarg($releasePath).' 2>&1';

        $output = null;
        $resultCode = null;

        exec($commandToRun, $output, $resultCode);

        if($resultCode > 0) {
            $this->log('Could not clone repository: '.json_encode($output));

            return $resultCode;
        }

        $this->log('Repository cloned');

        return 0;
    }
}