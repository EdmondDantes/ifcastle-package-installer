<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use IfCastle\Application\Bootloader\BootManager\BootManagerByDirectory;
use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\BootManager\Exceptions\PackageNotFound;
use IfCastle\OsUtilities\Safe;

final class Installer extends LibraryInstaller
{
    public const string PREFIX        = '  - ';

    public const string IFCASTLE      = '<bg=bright-blue;options=bold> IfCastle </>';

    #[\Override]
    public function supports(string $packageType)
    {
        return \str_starts_with($packageType, 'ifcastle-');
    }

    #[\Override]
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        return parent::install($repo, $package)->then(function () use ($package) {

            $extraConfig            = $package->getExtra();

            if ($extraConfig === [] || empty($extraConfig['ifcastle-installer'])) {
                return;
            }

            $packageInstaller       = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $package);

            $packageInstaller->install();

            $this->io->write(self::PREFIX . self::IFCASTLE . " installed package: <info>{$package->getName()}</info>");
        });
    }

    #[\Override]
    public function update(
        InstalledRepositoryInterface $repo,
        PackageInterface             $initial,
        PackageInterface             $target
    ): void {
        parent::update($repo, $initial, $target)->then(function () use ($initial, $target) {

            $extraConfig            = $target->getExtra();

            if ($extraConfig === [] || empty($extraConfig['ifcastle-installer'])) {
                return;
            }

            $packageInstaller       = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $target);

            $packageInstaller->update();

            $this->io->write(self::PREFIX . self::IFCASTLE . " updated package: <info>{$target->getName()}</info>");
        });
    }

    #[\Override]
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package): void
    {
        $extraConfig                = $package->getExtra();

        if ($extraConfig === [] || empty($extraConfig['ifcastle-installer'])) {
            return;
        }

        $packageInstaller           = $this->instanciatePackageInstaller($extraConfig['ifcastle-installer'], $package);

        try {
            $packageInstaller->uninstall();
            $this->io->write(self::PREFIX . self::IFCASTLE . " uninstalled package: <info>{$package->getName()}</info>");
        } catch (PackageNotFound) {
        }

        parent::uninstall($repo, $package);
    }

    private function instanciatePackageInstaller(array $installerConfig, PackageInterface $package): PackageInstallerInterface
    {
        if (empty($installerConfig['installer-class'])) {
            return (new PackageInstallerDefault(
                $this->instanciateBootManager(), new ZeroContext($this->getProjectDir()))
            )->setConfig($installerConfig, $package->getName());
        }

        $installerClass             = $installerConfig['installer-class'];

        if (!\class_exists($installerClass)) {
            throw new \RuntimeException(
                "Installer class {$installerClass} not found for package {$package->getName()}"
            );
        }

        if (\is_subclass_of($installerClass, PackageInstallerInterface::class)) {
            throw new \RuntimeException(
                "Installer class {$installerClass} must implement PackageInstallerInterface for package {$package->getName()}"
            );
        }

        return new $installerClass($this->instanciateBootManager(), new ZeroContext($this->getProjectDir()));
    }

    private function getProjectDir(): string
    {
        return \realpath($this->vendorDir . '/..');
    }

    private function instanciateBootManager(): BootManagerInterface
    {
        $bootloaderDir              = $this->getProjectDir() . '/bootloader';

        if (!\is_dir($bootloaderDir)) {
            Safe::execute(fn() => \mkdir($bootloaderDir));
        }

        if (!\is_dir($bootloaderDir)) {
            throw new \RuntimeException('Bootloader directory is not exist: ' . $bootloaderDir);
        }

        $bootManagerFile            = $bootloaderDir . '/boot-manager.php';

        if (\file_exists($bootManagerFile)) {
            return include $bootManagerFile;
        }

        return new BootManagerByDirectory($bootloaderDir);

    }
}
