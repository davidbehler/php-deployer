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
        echo 'deploy start';

        $returnCode = $this->getApplication()->doRun(new ArrayInput([
            'command' => 'deploy:ensure-directory-structure'
        ]), $output);

        dump($returnCode);

        echo 'deploy end';

        return 0;
    }
}