<?php
declare(strict_types=1);

namespace IfCastle\PackageInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

final class Plugin                  implements PluginInterface
{
    #[\Override]
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer                  = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
    
    #[\Override]
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }
    
    #[\Override]
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}