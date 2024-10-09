<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;

final class InstallerApplication    extends ApplicationAbstract
{
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}