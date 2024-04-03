<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class CompareVersionCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:compare-version';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));

        $currentReleasePath = $this->releaseManager->getCurrentReleasePath();

        $currentVersion = null;
        $newVersion = null;

        if($currentReleasePath) {
            $filesystem = new Filesystem;

            if($filesystem->exists($currentReleasePath)) {
                $commandToRun = 'git --git-dir '.escapeshellarg($currentReleasePath.'.git').' rev-parse HEAD 2>&1';

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