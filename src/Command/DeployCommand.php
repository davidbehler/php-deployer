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
        $this->log('Deployment started');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $releasePath = RELEASES_PATH.'release-'.$now->format('Y-m-d').'-'.$now->getTimestamp().'/';

        $commandsToRun = [
            'deploy:ensure-directory-structure' => [
                'options' => [
                    'releasePath' => $releasePath
                ]
            ],
            'deploy:clone-repository' => [
                'options' => [
                    'releasePath' => $releasePath
                ]
            ],
            'deploy:compare-version' => [
                'options' => [
                    'releasePath' => $releasePath
                ],
                'nonFailureStopCode' => 2
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