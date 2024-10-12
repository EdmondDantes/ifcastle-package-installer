<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\ServiceManager\ServiceManagerInterface;

/**
 * Class for instantiating the application core for working with services in installation mode.
 */
final class InstallerApplication    extends ApplicationAbstract
{
    public const string APP_CODE    = 'installer';
    
    public function getSystemEnvironment(): SystemEnvironmentInterface
    {
        return $this->systemEnvironment;
    }
    
    public function getServiceManager(): ServiceManagerInterface
    {
        return $this->systemEnvironment->resolveDependency(ServiceManagerInterface::class);
    }
    
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}