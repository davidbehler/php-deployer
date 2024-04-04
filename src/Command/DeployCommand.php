<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends BaseCommand
{
    protected static $defaultName = 'deploy';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentDeploymentIdentifier = $this->releaseManager->getCurrentDeploymentIdentifier();

        if($currentDeploymentIdentifier) {
            $this->log('Deployment could not be started because another deployment is currently in progress: '.$currentDeploymentIdentifier);

            return 0;
        }

        $deploymentIdentifier = $this->releaseManager->getDeploymentIdentifier();

        $this->log('Deployment started ('.$deploymentIdentifier.')');

        $this->releaseManager->setCurrentDeploymentIdentifier($deploymentIdentifier);

        $commandsToRun = [
             [
                'command' => 'deploy:ensure-directory-structure',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier
                ]
            ],
            [
                'command' => 'deploy:clone-repository',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier,
                    'deploymentUser' => $_ENV['DEPLOYMENT_USER']
                ]
            ],
            [
                'command' => 'deploy:compare-version',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier
                ],
                'nonFailureStopCode' => 2
            ],
            [
                'command' => 'deploy:ensure-proper-owner',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier,
                    'deploymentOwner' => $_ENV['DEPLOYMENT_USER']
                ],
            ],
            [
                'command' => 'deploy:run-release-commands',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier,
                    'deploymentOwner' => $_ENV['DEPLOYMENT_USER'],
                    'deploymentState' => 'post-clone'
                ],
            ],
            [
                'command' => 'deploy:ensure-proper-owner',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier,
                    'deploymentOwner' => $_ENV['DEPLOYMENT_USER']
                ],
            ],
            [
                'command' => 'deploy:update-current-release-link',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier
                ],
            ],
            [
                'command' => 'deploy:run-release-commands',
                'options' => [
                    'deploymentIdentifier' => $deploymentIdentifier,
                    'deploymentOwner' => $_ENV['DEPLOYMENT_USER'],
                    'deploymentState' => 'post-update-link'
                ],
            ],
        ];

        try {
            foreach($commandsToRun as $config) {
                $options = isset($config['options']) ? $config['options'] : [];

                $command = $config['command'];

                $commandConfig = [
                    'command' => $command
                ];

                foreach($options as $name => $value) {
                    $commandConfig['--'.$name] = $value;
                }

                $this->log('Running '.$command);

                $returnCode = $this->getApplication()->doRun(new ArrayInput($commandConfig), $output);

                if(isset($config['nonFailureStopCode']) and $config['nonFailureStopCode'] == $returnCode) {
                    $this->log('Non-failure stop of deployment triggered: '.$command);

                    $this->releaseManager->deleteRelease($deploymentIdentifier);

                    break;
                }

                if($returnCode != 0) {
                    throw new \Exception($command);
                }
            }

            $this->releaseManager->clearCurrentDeploymentIdentifier();

            $this->log('Deployment ended ('.$deploymentIdentifier.')');
        } catch (\Exception $e) {
            $this->releaseManager->clearCurrentDeploymentIdentifier();

            $this->releaseManager->deleteRelease($deploymentIdentifier);

            $this->log('Deployment failed ('.$deploymentIdentifier.'): '.$e->getMessage());
        }

        return 0;
    }
}