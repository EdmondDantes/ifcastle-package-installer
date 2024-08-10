<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;

final class PackageInstallerDefault implements PackageInstallerInterface
{
    private array $config           = [];
    private string $packageName      = '';
    
    public function __construct(private BootManagerInterface $bootManager, private ZeroContextInterface $zeroContext) {}

    public function setConfig(array $config, string $packageName): self
    {
        $this->config               = $config;
        $this->packageName          = $packageName;
        
        return $this;
    }
    
    #[\Override]
    public function install(): void
    {
        $installerConfig            = $this->config;
        
        if(!empty($installerConfig['bootloader'])) {
            
            if(empty($installerConfig['bootloader']['bootloaders'])) {
                throw new \RuntimeException("Bootloaders not found in installer config for package {$this->packageName}");
            }
            
            $this->bootManager->addBootloader(
                $this->packageName,
                $installerConfig['bootloader']['bootloaders'],
                !empty($installerConfig['bootloader']['for_applications']) ? $installerConfig['bootloader']['for_applications'] : []
            );
        }
    }
    
    #[\Override]
    public function update(): void
    {
        // TODO: Implement update() method.
    }
    
    #[\Override]
    public function uninstall(): void
    {
        // TODO: Implement uninstall() method.
    }
}