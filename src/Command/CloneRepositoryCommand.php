<?php
namespace PhpDeployer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class CloneRepositoryCommand extends BaseCommand
{
    protected static $defaultName = 'deploy:clone-repository';

    protected function configure(): void
    {
        $this->addOption('releasePath', null, InputOption::VALUE_REQUIRED, 'Release path', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $releasePath = $input->getOption('releasePath');

        $this->log('Clone repository into '.$releasePath);

        // run_command_exit_on_error "git clone --depth 1 $REPOSITORY $DEPLOY_RELEASE_PATH"

        // $process = Process::fromShellCommandline('sudo -u "${:CLONE_USER}" -H git clone --depth 1 "${:CLONE_REPOSITORY}" "${:CLONE_TARGET_PATH}"', null, [
        //     'CLONE_USER' => getenv('DEPLOYMENT_USER'),
        //     'CLONE_REPOSITORY' => getenv('REPOSITORY'),
        //     'CLONE_TARGET_PATH' => $releasePath
        // ]);

        $process = new Process(['git clone --depth 1 '.$_ENV['REPOSITORY'].' '.$releasePath], null);

        ob_start();

        $output = shell_exec('sudo -u '.$_ENV['DEPLOYMENT_USER'].' -H git clone --depth 1 '.$_ENV['REPOSITORY'].' '.escapeshellarg($releasePath));

        $output = ob_get_contents();

        ob_end_clean();

        $this->log($output);

        exit();
        // exit();

        // $process->run();

        // // executes after the command finishes
        // if (!$process->isSuccessful()) {
        //     $this->log('Could not clone repository: '.$process->getErrorOutput());

        //     return 1;
        // }

        $this->log('Repository cloned');

        return 0;
    }
}