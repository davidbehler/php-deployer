<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class UpdateCurrentReleaseLinkCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:update-current-release-link';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deploymentIdentifier = $input->getOption('deploymentIdentifier');

        $this->releaseManager->setCurrentReleaseIdentifier($deploymentIdentifier);

        $this->log('releases.json update');

        $this->releaseManager->updateCurrentLink($deploymentIdentifier);

        $this->log('current link update');

        return 0;
    }
}