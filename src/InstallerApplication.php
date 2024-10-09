<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;

/**
 * Class for instantiating the application core for working with services in installation mode.
 */
final class InstallerApplication    extends ApplicationAbstract
{
    public const string APP_CODE    = 'installer';
    
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}