<?php

namespace PhpDeployer\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ReleaseManager
{
    private LoggerInterface $logger;
    private string $projectPath;

    private string $configFileName = 'releases.json';

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

    public function writeConfig(string $currentPath, array $releases)
    {
        $releaseConfig = [
            'current' => $currentPath,
            'releases' => $releases
        ];

        $releasesConfigPath = $this->projectPath.$this->configFileName;

        file_put_contents($releasesConfigPath, json_encode($releaseConfig));

        return $this;
    }
}