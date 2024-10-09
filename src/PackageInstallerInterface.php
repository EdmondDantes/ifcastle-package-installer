<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use IfCastle\Application\Bootloader\BootManager\BootManagerInterface;
use IfCastle\Application\Bootloader\Builder\ZeroContextInterface;

interface PackageInstallerInterface
{
    public function __construct(
        BootManagerInterface $bootManager,
        ZeroContextInterface $zeroContext
    );
    
    public function install(): void;
    
    public function update(): void;
    
    public function uninstall(): void;
}