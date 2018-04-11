<?php

namespace KenderTas\Installer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\InstallerEvents;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;
use Composer\Util\Filesystem;
use Composer\Package\PackageInterface;

/**
 * Class CoreManager
 * @package AydinHassan\MagentoCoreComposerInstaller
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CoreManager implements PluginInterface, EventSubscriberInterface {

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Vendor Directory
     *
     * @var string
     */
    protected $vendorDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Output Prefix
     *
     * @var string
     */
    protected $ioPrefix = '  - <comment>KenderTas Installer: </comment>';

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io) {
        $this->composer = $composer;
        $this->io = $io;
        $this->vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        $this->filesystem = new Filesystem();
    }

    /**
     * @param PackageInterface $package
     * @return string
     */
    public function getInstallPath(PackageInterface $package) {
        $targetDir = $package->getTargetDir();

        if ($targetDir) {
            return sprintf('%s/%s', $this->getPackageBasePath($package), $targetDir);
        }

        return $this->getPackageBasePath($package);
    }

    /**
     * @param PackageInterface $package
     * @return string
     */
    protected function getPackageBasePath(PackageInterface $package) {
        $this->filesystem->ensureDirectoryExists($this->vendorDir);
        $this->vendorDir = realpath($this->vendorDir);

        return ($this->vendorDir ? $this->vendorDir . '/' : '') . $package->getPrettyName();
    }

    /**
     * Tell event dispatcher what events we want to subscribe to
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            InstallerEvents::POST_DEPENDENCIES_SOLVING => [
                ['checkCoreDependencies', 0]
            ],
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['installCore', 0]
            ],
            PackageEvents::PRE_PACKAGE_UPDATE => [
                ['uninstallCore', 0]
            ],
            PackageEvents::POST_PACKAGE_UPDATE => [
                ['installCore', 0]
            ],
            PackageEvents::PRE_PACKAGE_UNINSTALL => [
                ['uninstallCore', 0]
            ],
        ];
    }

    /**
     * Check that there is only 1 core package required
     */
    public function checkCoreDependencies(InstallerEvent $event) {
        return true;
    }


    /**
     * @param PackageEvent $event
     */
    public function installCore(PackageEvent $event) {
        switch ($event->getOperation()->getJobType()) {
            case "install":
                $package = $event->getOperation()->getPackage();
                break;
            case "update":
                $package = $event->getOperation()->getTargetPackage();
                break;
        }

        $this->io->write(
            sprintf(
                '%s<info>Installing: "%s" version: "%s"</info>',
                $this->ioPrefix,
                $package->getPrettyName(),
                $package->getVersion()
            )
        );

    }

    /**
     * @param PackageEvent $event
     */
    public function uninstallCore(PackageEvent $event) {

    }
}
