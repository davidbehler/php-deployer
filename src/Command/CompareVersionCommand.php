<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class CompareVersionCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:compare-version';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deploymentIdentifier = $input->getOption('deploymentIdentifier');
        $currentReleaseIdentifier = $this->releaseManager->getCurrentReleaseIdentifier();

        $currentVersion = null;

        if($currentReleaseIdentifier) {
            $currentVersion = $this->releaseManager->getReleaseCommitId($currentReleaseIdentifier);
        }

        $newVersion = $this->releaseManager->getReleaseCommitId($deploymentIdentifier);

        if($currentVersion and $newVersion and $newVersion == $currentVersion) {
            return 2;
        }

        return 0;
    }
}