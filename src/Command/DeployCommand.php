<?php
namespace PhpDeployer\Command;

use PhpDeployer\Service\ReleaseManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeployCommand extends BaseCommand
{
    protected static $defaultName = 'deploy';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->log('Deployment started');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $targetReleasePath = RELEASES_PATH.'release-'.$now->format('Y-m-d').'-'.$now->getTimestamp().'/';

        $commandsToRun = [
            'deploy:ensure-directory-structure' => [
                'options' => [
                    'releasePath' => $targetReleasePath
                ]
            ],
            'deploy:clone-repository' => [
                'options' => [
                    'releasePath' => $targetReleasePath
                ]
            ],
            'deploy:compare-version' => [
                'options' => [
                    'releasePath' => $targetReleasePath
                ],
                'nonFailureStopCode' => 2
            ],
            'deploy:update-current-release-link' => [
                'options' => [
                    'releasePath' => $targetReleasePath
                ],
            ]
        ];

        try {
            foreach($commandsToRun as $command => $config) {
                $options = isset($config['options']) ? $config['options'] : [];

                $commandConfig = [
                    'command' => $command
                ];

                foreach($options as $name => $value) {
                    $commandConfig['--'.$name] = $value;
                }

                $returnCode = $this->getApplication()->doRun(new ArrayInput($commandConfig), $output);

                if(isset($config['nonFailureStopCode']) and $config['nonFailureStopCode'] == $returnCode) {
                    $this->log('Non-failure stop of deployment triggered: '.$command);

                    $this->getApplication()->doRun(new ArrayInput([
                        'command' => 'deploy:cleanup-release',
                        '--releasePath' => $targetReleasePath
                    ]), $output);

                    break;
                }

                if($returnCode != 0) {
                    throw new \Exception($command);
                }
            }

            $this->log('Deployment ended');
        } catch (\Exception $e) {
            $this->log('Deployment failed running '.$e->getMessage());
        }

        return 0;
    }
}