<?php

namespace PhpDeployer\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ReleaseManager
{
    private LoggerInterface $logger;
    private string $projectPath;

    private string $configFileName = 'releases.json';
    private string $currentDeploymentFileName = 'current-deployment';

    public function __construct(LoggerInterface $logger, string $projectPath)
    {
        $this->logger = $logger;

        $this->projectPath = $projectPath;
    }

    public function getConfig(): array
    {
        $filesystem = new Filesystem;

        $releasesConfigPath = $this->projectPath.$this->configFileName;

        if($filesystem->exists($releasesConfigPath)) {
            return json_decode(file_get_contents($releasesConfigPath), true);
        }

        return [];
    }

    public function setCurrentReleaseIdentifier(string $releaseIdentifier)
    {
        $config = $this->getConfig();

        $config['current'] = $releaseIdentifier;

        $releases = isset($config['releases']) ? $config['releases'] : [];

        $releases[$releaseIdentifier] = [
            'path' => $this->getReleasePath($releaseIdentifier),
            'releasedOn' => (new \DateTime('now'))->format('Y-m-d H:i:s'),
            'commitId' => $this->getReleaseCommitId($releaseIdentifier)
        ];

        $config['releases'] = $releases;

        $this->setConfig($config);
    }

    public function setConfig(array $config)
    {
        $releasesConfigPath = $this->projectPath.$this->configFileName;

        file_put_contents($releasesConfigPath, json_encode($config));

        return $this;
    }

    public function getDeploymentIdentifier()
    {
        $now = new \DateTime('now');

        return 'release-'.$now->format('Y-m-d').'-'.$now->getTimestamp().'-'.hash('sha256', $now->format('Y-m-d H:i:s'));
    }

    public function getCurrentDeploymentIdentifier(): string
    {
        $filesystem = new Filesystem;

        $currentDeploymentPath = $this->projectPath.$this->currentDeploymentFileName;

        if($filesystem->exists($currentDeploymentPath)) {
            return file_get_contents($currentDeploymentPath);
        }

        return '';
    }

    public function setCurrentDeploymentIdentifier(string $currentDeploymentIdentifier)
    {
        $currentDeploymentPath = $this->projectPath.$this->currentDeploymentFileName;

        file_put_contents($currentDeploymentPath, $currentDeploymentIdentifier);
    }

    public function clearCurrentDeploymentIdentifier()
    {
        $this->setCurrentDeploymentIdentifier('');
    }

    public function getReleasePath(string $deploymentIdentifier): string
    {
        return $this->projectPath.'releases/'.$deploymentIdentifier.'/';
    }

    public function getCurrentReleaseIdentifier(): ?string
    {
        $config = $this->getConfig();

        if(isset($config['current']) and $config['current']) {
            return $this->getReleasePath($config['current']);
        }

        return null;
    }

    public function getCurrentReleasePath(): ?string
    {
        $currentReleaseIdentifier = $this->getCurrentReleaseIdentifier();

        if($currentReleaseIdentifier) {
            return $this->getReleasePath($currentReleaseIdentifier);
        }

        return null;
    }

    public function updateCurrentLink(string $releaseIdentifier)
    {
        $filesystem = new Filesystem;

        $filesystem->symlink($this->getReleasePath($releaseIdentifier), 'releases/current');
    }

    public function getReleaseCommitId(string $releaseIdentifier): ?string
    {
        $releasePath = $this->getReleasePath($releaseIdentifier);

        $filesystem = new Filesystem;

        if($filesystem->exists($releasePath)) {
            $commandToRun = 'git --git-dir '.escapeshellarg($releasePath.'.git').' rev-parse HEAD 2>&1';

            $output = null;
            $resultCode = null;

            exec($commandToRun, $output, $resultCode);

            if($resultCode == 0) {
                return reset($output);
            }
        }

        return null;
    }
}