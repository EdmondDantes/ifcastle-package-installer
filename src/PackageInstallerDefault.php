<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;

final class PackageInstallerDefault implements PackageInstallerInterface
{
    public const string PACKAGE     = 'package';
    
    private array $config           = [];
    private string $packageName      = '';
    
    public function __construct(
        private readonly BootManagerInterface $bootManager,
        private readonly ZeroContextInterface $zeroContext
    ) {}

    public function setConfig(array $config, string $packageName): self
    {
        $this->config               = $config;
        $this->packageName          = $packageName;
        
        if(!empty($config[self::PACKAGE]) && !empty($config[self::PACKAGE]['name'])) {
            $this->packageName      = $config[self::PACKAGE]['name'];
        }
        
        return $this;
    }
    
    #[\Override]
    public function install(): void
    {
        $installerConfig            = $this->config;
        
        if(!empty($installerConfig[self::PACKAGE])) {
            
            if(empty($installerConfig[self::PACKAGE]['bootloaders'])) {
                throw new \RuntimeException("Bootloaders not found in installer config for package {$this->packageName}");
            }
            
            $this->bootManager->addBootloader(
                $this->packageName,
                $installerConfig[self::PACKAGE]['bootloaders'],
                !empty($installerConfig[self::PACKAGE]['for_applications']) ? $installerConfig[self::PACKAGE]['for_applications'] : []
            );
        }
    }
    
    #[\Override]
    public function update(): void
    {
        $this->install();
    }
    
    #[\Override]
    public function uninstall(): void
    {
        $this->bootManager->removeBootloader($this->packageName);
    }
}