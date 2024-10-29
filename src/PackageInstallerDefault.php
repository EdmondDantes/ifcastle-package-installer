<?php

declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;
use IfCastle\Application\EngineRolesEnum;
use IfCastle\Application\Runner;
use IfCastle\ServiceManager\ServiceDescriptor;

final class PackageInstallerDefault implements PackageInstallerInterface
{
    public const string PACKAGE     = 'package';

    public const string SERVICES    = 'services';

    public const string NAME        = 'name';

    public const string IS_ACTIVE   = 'isActive';

    public const string RUNTIME_TAGS = 'runtimeTags';

    public const string EXCLUDE_TAGS = 'excludeTags';

    public const string BOOTLOADERS  = 'bootloaders';

    public const string APPLICATIONS = 'applications';

    public const string GROUPS       = 'groups';

    public const string GROUP        = 'group';

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

        if (!empty($config[self::PACKAGE]) && !empty($config[self::PACKAGE][self::NAME])) {
            $this->packageName      = $config[self::PACKAGE][self::NAME];
        }

        return $this;
    }

    #[\Override]
    public function install(): void
    {
        $installerConfig            = $this->config;

        if (!empty($installerConfig[self::PACKAGE])) {

            if (!empty($installerConfig[self::PACKAGE][self::GROUPS])
               && !empty($installerConfig[self::PACKAGE][self::BOOTLOADERS])) {
                throw new \RuntimeException("Group and Bootloaders cannot be defined at the same time for package {$this->packageName}");
            }

            if (!empty($installerConfig[self::PACKAGE][self::GROUPS])) {
                $this->installBootloaders($installerConfig[self::PACKAGE][self::GROUPS]);
            } elseif (!empty($installerConfig[self::PACKAGE][self::BOOTLOADERS])) {
                $this->installBootloaders([$installerConfig[self::PACKAGE]]);
            } else {
                throw new \RuntimeException("Bootloaders or Groups must be defined for package {$this->packageName}");
            }
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

        $this->bootManager->removeComponent($this->packageName);
    }
    
    /**
     * @throws \Throwable
     */
    private function getInstaller(): InstallerApplication
    {
        if ($this->installerApplication === null) {

            $runner                 = new Runner(
                $this->zeroContext->getApplicationDirectory(),
                InstallerApplication::APP_CODE,
                InstallerApplication::class,
                [EngineRolesEnum::CONSOLE->value]
            );

            $this->installerApplication = $runner->run();
        }

        return $this->installerApplication;
    }

    private function installBootloaders(array $bootloaderGroups): void
    {
        $component                  = $this->bootManager->createComponent($this->packageName);

        foreach ($bootloaderGroups as $group) {
            $component->add(
                bootloaders : $group[self::BOOTLOADERS],
                applications: $group[self::APPLICATIONS] ?? [],
                runtimeTags : $group[self::RUNTIME_TAGS] ?? [],
                excludeTags : $group[self::EXCLUDE_TAGS] ?? [],
                isActive    : $group[self::IS_ACTIVE] ?? true,
                group       : $group[self::GROUP] ?? null
            );
        }

        $this->bootManager->addComponent($component);
    }

    private function installServices(array $services): void
    {
        $serviceManager             = $this->getInstaller()->getServiceManager();

        foreach ($services as $serviceConfig) {

            $serviceName            = $serviceConfig[Service::NAME] ?? throw new \RuntimeException('Service name is not defined');

            $serviceDescriptor      = new ServiceDescriptor(
                serviceName  : $serviceName,
                className    : $serviceConfig[Service::CLASS_NAME]  ?? throw new \RuntimeException("Service class is not found for service $serviceName"),
                isActive     : $serviceConfig[Service::IS_ACTIVE]   ?? false,
                config       : $serviceConfig[Service::CONFIG]      ?? [],
                includeTags  : $serviceConfig[Service::TAGS]        ?? [],
                excludeTags  : $serviceConfig[Service::EXCLUDE_TAGS] ?? []
            );

            $serviceManager->installService($serviceDescriptor);
        }
    }

    private function uninstallServices(array $services): void
    {
        $serviceManager             = $this->getInstaller()->getServiceManager();

        foreach ($services as $serviceConfig) {
            $serviceName            = $serviceConfig[Service::NAME] ?? '';

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
