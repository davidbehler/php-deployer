<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class RunReleaseCommandsCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:run-release-commands';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
        $this->addOption('deploymentOwner', null, InputOption::VALUE_REQUIRED, 'Deployment owner', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));
        $deploymentOwner = $input->getOption('deploymentOwner');

        $commandsJsonPath = $releasePath.'bin/deployment-commands.json';

        $filesystem = new Filesystem;

        if($filesystem->exists($commandsJsonPath)) {
            $commands = json_decode(file_get_contents($commandsJsonPath), true);
        } else {
            $commands = [
                'composer-install' => [
                    'command' => 'composer install --working-dir=#releasePath#',
                    'user' => '#deploymentUser#'
                ]
            ];
        }

        foreach($commands as $commandLabel => $commandConfig) {
            $commandPrefix = '';

            if(isset($commandConfig['user']) and $commandConfig['user']) {
                $commandConfig['user'] = str_replace('#deploymentUser#', $deploymentOwner, $commandConfig['user']);

                $commandPrefix = 'sudo -u '.$commandConfig['user'].' -H';
            }

            $commandToRun = str_replace('#releasePath#', escapeshellarg($releasePath), $commandConfig['command']);

            if($commandPrefix) {
                $commandToRun = $commandPrefix.' '.$commandToRun;
            }

            $commandToRun .= ' 2>&1';

            $output = null;
            $resultCode = null;

            exec($commandToRun, $output, $resultCode);

            if($resultCode > 0) {
                $this->log('Could not run release command ('.$commandLabel.'): '.json_encode($output));

                return $resultCode;
            }
        }

        return 0;
    }
}