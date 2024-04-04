<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

use function PHPSTORM_META\map;

class RunReleaseCommandsCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:run-release-commands';

    protected function configure(): void
    {
        $this->addOption('deploymentIdentifier', null, InputOption::VALUE_REQUIRED, 'Deployment identifier', null);
        $this->addOption('deploymentOwner', null, InputOption::VALUE_REQUIRED, 'Deployment owner', null);
        $this->addOption('deploymentState', null, InputOption::VALUE_REQUIRED, 'Deployment state', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $this->releaseManager->getReleasePath($input->getOption('deploymentIdentifier'));
        $sharedPath = PROJECT_PATH.'shared/';
        $deploymentOwner = $input->getOption('deploymentOwner');
        $deploymentState = $input->getOption('deploymentState');

        $commandsJsonPath = $releasePath.'bin/deployment-commands.json';

        $filesystem = new Filesystem;

        if($filesystem->exists($commandsJsonPath)) {
            $commands = json_decode(file_get_contents($commandsJsonPath), true);
        } else {
            $commands = [
                'composer-install' => [
                    'command' => 'composer install --working-dir=#releasePath# --no-dev --no-interaction --optimize-autoloader',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'ensure-directories-exist' => [
                    'command' => 'php #releasePath#bin/console deployment:ensure-directories-exist --sharedPath=#sharedPath#',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'run-migrations' => [
                    'command' => 'php #releasePath#bin/phpmig migrate',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'download-certificates' => [
                    'command' => 'php #releasePath#bin/console misc:download-cacert',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'update-geo-ip-database' => [
                    'command' => 'php #releasePath#bin/console misc:update-geo-ip-database',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'generate-sitemap' => [
                    'command' => 'php #releasePath#bin/console seo:generate-sitemap',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'update-assets-cache' => [
                    'command' => 'php #releasePath#bin/console assets:update-cache-control',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'update-user-listing-option-cache' => [
                    'command' => 'php #releasePath#bin/console misc:update-listing-filter-options-cache',
                    'user' => '#deploymentUser#',
                    'state' => 'post-clone'
                ],
                'reload-php' => [
                    'command' => 'service php8.1-fpm reload',
                    'state' => 'post-update-link'
                ]
            ];
        }

        foreach($commands as $commandLabel => $commandConfig) {
            if($commandConfig['state'] == $deploymentState) {
                $commandPrefix = '';

                if(isset($commandConfig['user']) and $commandConfig['user']) {
                    $commandConfig['user'] = str_replace('#deploymentUser#', $deploymentOwner, $commandConfig['user']);

                    $commandPrefix = 'sudo -u '.$commandConfig['user'].' -H';
                }

                $commandToRun = str_replace(['#releasePath#', '#sharedPath#'], [escapeshellarg($releasePath), escapeshellarg($sharedPath)], $commandConfig['command']);

                if($commandPrefix) {
                    $commandToRun = $commandPrefix.' '.$commandToRun;
                }

                $commandToRun .= ' 2>&1';

                $output = null;
                $resultCode = null;

                $this->log('Running release command: '.$commandLabel);

                exec($commandToRun, $output, $resultCode);

                if($resultCode > 0) {
                    $this->log('Release command failed ('.$commandLabel.'): '.json_encode($output));

                    return $resultCode;
                } else {
                    $this->log('Completed release command: '.$commandLabel);
                }
            }
        }

        return 0;
    }
}