<?php
namespace PhpDeployer\Command;

use PhpDeployer\Service\ReleaseManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class CompareVersionCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:compare-version';

    protected function configure(): void
    {
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $releaseManager = new ReleaseManager($this->logger, PROJECT_PATH);

        $releasesConfig = $releaseManager->getConfig();

        $currentVersion = null;
        $newVersion = null;

        if(isset($releasesConfig['current'])) {
            $filesystem = new Filesystem;

            if($filesystem->exists($releasesConfig['current'])) {
                $commandToRun = 'git --git-dir '.escapeshellarg($releasesConfig['current'].'.git').' rev-parse HEAD 2>&1';

                $output = null;
                $resultCode = null;

                exec($commandToRun, $output, $resultCode);

                if($resultCode == 0) {
                    $currentVersion = reset($output);
                }
            }
        }

        $filesystem = new Filesystem;

        if($filesystem->exists($releasePath)) {
            $commandToRun = 'git --git-dir '.escapeshellarg($releasePath.'.git').' rev-parse HEAD 2>&1';

            $output = null;
            $resultCode = null;

            exec($commandToRun, $output, $resultCode);

            if($resultCode == 0) {
                $newVersion = reset($output);
            }
        }

        if($newVersion == $currentVersion) {
            return 2;
        }

        return 0;
    }
}