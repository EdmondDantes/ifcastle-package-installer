<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;

readonly final class ZeroContext implements ZeroContextInterface
{
    public function __construct(private string $appDir) {}
    
    
    #[\Override]
    public function getApplicationDirectory(): string
    {
        return $this->appDir;
    }
    
    #[\Override]
    public function getApplicationType(): string
    {
        return '';
    }
    
    #[\Override]
    public function getExecutionRoles(): array
    {
        return [];
    }
}