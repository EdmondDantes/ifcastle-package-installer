<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;
use IfCastle\ServiceManager\ServiceDescriptor;

final class PackageInstallerDefault implements PackageInstallerInterface
{
    public const string PACKAGE     = 'package';

    public const string SERVICES    = 'services';

    private array $config           = [];

    private string $packageName      = '';

    private InstallerApplication|null $installerApplication = null;

    public function __construct(
        private readonly BootManagerInterface $bootManager,
        private readonly ZeroContextInterface $zeroContext
    ) {}

    public function setConfig(array $config, string $packageName): self
    {
        $this->config               = $config;
        $this->packageName          = $packageName;

        if (!empty($config[self::PACKAGE]) && !empty($config[self::PACKAGE]['name'])) {
            $this->packageName      = $config[self::PACKAGE]['name'];
        }

        return $this;
    }

    #[\Override]
    public function install(): void
    {
        $installerConfig            = $this->config;

        if (!empty($installerConfig[self::PACKAGE])) {

            if (empty($installerConfig[self::PACKAGE]['bootloaders'])) {
                throw new \RuntimeException("Bootloaders not found in installer config for package {$this->packageName}");
            }

            $this->bootManager->addBootloader(
                $this->packageName,
                $installerConfig[self::PACKAGE]['bootloaders'],
                empty($installerConfig[self::PACKAGE]['for_applications']) ? [] : $installerConfig[self::PACKAGE]['for_applications']
            );
        }

        if (!empty($installerConfig[self::SERVICES]) && \is_array($installerConfig[self::SERVICES])) {
            $this->installServices($installerConfig[self::SERVICES]);
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
        if (!empty($this->config[self::SERVICES]) && \is_array($this->config[self::SERVICES])) {
            $this->uninstallServices($this->config[self::SERVICES]);
        }

        $this->bootManager->removeBootloader($this->packageName);
    }

    private function getInstaller(): InstallerApplication
    {
        if ($this->installerApplication === null) {
            $this->installerApplication = InstallerApplication::run(
                $this->zeroContext->getApplicationDirectory(), null, false
            );
        }

        return $this->installerApplication;
    }

    private function installServices(array $services): void
    {
        $serviceManager             = $this->getInstaller()->getServiceManager();

        foreach ($services as $serviceConfig) {

            $serviceName            = $serviceConfig['name'] ?? throw new \RuntimeException('Service name is not defined');

            $serviceDescriptor      = new ServiceDescriptor(
                serviceName  : $serviceName,
                className    : $serviceConfig['class']      ?? throw new \RuntimeException("Service class is not found for service $serviceName"),
                isActive     : $serviceConfig['isActive']   ?? false,
                config       : $serviceConfig['config']     ?? [],
                includeTags  : $serviceConfig['tags']       ?? [],
                excludeTags  : $serviceConfig['excludeTags'] ?? []
            );

            $serviceManager->installService($serviceDescriptor);
        }
    }

    private function uninstallServices(array $services): void
    {
        $serviceManager             = $this->getInstaller()->getServiceManager();

        foreach ($services as $serviceConfig) {
            $serviceName            = $serviceConfig['name'] ?? '';

            if ($serviceName === '') {
                continue;
            }

            try {
                $serviceManager->uninstallService($serviceName);
            } catch (\Exception $exception) {
                echo "Error uninstalling service $serviceName: {$exception->getMessage()}\n";
            }
        }
    }
}
