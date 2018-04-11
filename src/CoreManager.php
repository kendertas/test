<?php

namespace KenderTas\Installer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginEvents;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;
use Composer\Plugin\PreFileDownloadEvent;

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
     * Output Prefix
     *
     * @var string
     */
    protected $ioPrefix = '  - <comment>KenderTas Installer: </comment>';


    public static function getSubscribedEvents() {
        echo "getSubscribedEvents";
        return [
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['installCore', 0]
            ],
            PackageEvents::POST_PACKAGE_UPDATE => [
                ['installCore', 0]
            ]
        ];
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io) {

        $this->composer = $composer;
        $this->io = $io;
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
