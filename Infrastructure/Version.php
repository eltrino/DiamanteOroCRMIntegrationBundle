<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\OroCRMIntegrationBundle\Infrastructure;

use Oro\Bundle\PlatformBundle\Composer\LocalRepositoryFactory;

/**
 * Class Version
 *
 * @package Diamante\OroCRMIntegrationBundle\Infrastructure
 */
class Version
{
    /**
     * @var LocalRepositoryFactory
     */
    protected $composerRepository;

    /**
     * Version constructor.
     *
     * @param LocalRepositoryFactory $composerRepository
     */
    public function __construct(LocalRepositoryFactory $composerRepository)
    {
        $this->composerRepository = $composerRepository;
    }

    /**
     * @return string
     */
    public function getDiamanteVersion()
    {
        $version = $this->getVersion('diamante/orocrm-integration-bundle');

        return $version;
    }

    /**
     * @return string
     */
    public function getOroVersion()
    {
        $version = $this->getVersion('oro/platform');

        return $version;
    }

    /**
     * @param $packageName
     *
     * @return string
     */
    protected function getVersion($packageName)
    {
        $composerRepository = $this->composerRepository->getLocalRepository();
        $package = $composerRepository->findPackages($packageName)[0];
        $version = $package->getPrettyVersion();

        return $version;
    }
}
