<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use IfCastle\Application\Bootloader\BootManager\BootManagerByDirectory;
use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;

final class Installer               extends LibraryInstaller
{
    #[\Override]
    public function supports(string $packageType)
    {
        return str_starts_with($packageType, 'ifcastle-');
    }
    
    #[\Override]
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return parent::install($repo, $package)->then(function() use ($package) {
            
            $extraConfig            = $package->getExtra();
            
            if(empty($extraConfig) || empty($extraConfig['ifcastle-installer'])) {
                return;
            }
            
            $packageInstaller       = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $package);
            
            $packageInstaller->install();
            $this->io->write("IfCastle installed package: {$package->getName()}");
        });
    }
    
    #[\Override]
    public function update(
        InstalledRepositoryInterface $repo,
        PackageInterface             $initial,
        PackageInterface             $target
    )
    {
        parent::update($repo, $initial, $target)->then(function() use ($initial, $target) {
                
            $extraConfig            = $target->getExtra();
            
            if(empty($extraConfig) || empty($extraConfig['ifcastle-installer'])) {
                return;
            }
            
            $packageInstaller       = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $target);
            
            $packageInstaller->update();
            $this->io->write("IfCastle updated package: {$target->getName()}");
        });
    }
    
    #[\Override]
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $extraConfig                = $package->getExtra();
        
        if(empty($extraConfig) || empty($extraConfig['ifcastle-installer'])) {
            return;
        }
        
        $packageInstaller           = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $package);
        
        $packageInstaller->uninstall();
        $this->io->write("IfCastle uninstalled package: {$package->getName()}");
        
        parent::uninstall($repo, $package);
    }
    
    private function instanciatePackageInstaller(array $installerConfig, PackageInterface $package): PackageInstallerInterface
    {
        if(empty($installerConfig['installer-class'])) {
            return (new PackageInstallerDefault(
                $this->instanciateBootManager(), new ZeroContext($this->getProjectDir()))
            )->setConfig($installerConfig, $package->getName());
        }
        
        $installerClass             = $installerConfig['installer-class'];
        
        if (!class_exists($installerClass)) {
            throw new \RuntimeException(
                "Installer class {$installerClass} not found for package {$package->getName()}"
            );
        }
        
        if (is_subclass_of($installerClass, PackageInstallerInterface::class)) {
            throw new \RuntimeException(
                "Installer class {$installerClass} must implement PackageInstallerInterface for package {$package->getName()}"
            );
        }
        
        return new $installerClass($this->instanciateBootManager(), new ZeroContext($this->getProjectDir()));
    }
    
    private function getProjectDir(): string
    {
        return realpath($this->vendorDir . '/..');
    }
    
    private function instanciateBootManager(): BootManagerInterface
    {
        $projectDir                 = $this->getProjectDir();
        $bootManagerFile            = $projectDir . '/boot-manager.php';
        
        if(file_exists($bootManagerFile)) {
            return include $bootManagerFile;
        } else {
            return new BootManagerByDirectory($projectDir);
        }
    }
}