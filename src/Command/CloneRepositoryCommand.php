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
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $this->log('Clone repository into '.$releasePath);

        $commandToRun = 'sudo -u '.$_ENV['DEPLOYMENT_USER'].' -H git clone --depth 1 '.$_ENV['REPOSITORY'].' '.escapeshellarg($releasePath).' 2>&1';

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