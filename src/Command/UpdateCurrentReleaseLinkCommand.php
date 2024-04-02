<?php
namespace PhpDeployer\Command;

use PhpDeployer\Service\ReleaseManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class UpdateCurrentReleaseLinkCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:update-current-release-link';

    protected function configure(): void
    {
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $releaseManager = new ReleaseManager($this->logger, PROJECT_PATH);

        $releasesConfig = $releaseManager->getConfig();

        $releases = isset($releasesConfig['releases']) ? $releasesConfig['releases'] : [];

        $releases[] = $releasePath;

        $releaseManager->writeConfig($releasePath, $releases);

        $this->log('releases.json update');

        return 0;
    }
}