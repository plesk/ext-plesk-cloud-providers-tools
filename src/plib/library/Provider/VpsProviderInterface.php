<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Provider;

interface VpsProviderInterface
{
    /**
     * @param int $subscriptionId
     * @param array $additionalInfo
     * @return string $dumpId
     */
    public function deployDump($subscriptionId, $additionalInfo);

    /**
     * @param string $dumpId
     * @return bool
     */
    public function isDumpDeployed($dumpId);

    /**
     * @param string $dumpId
     * @param array $additionalInfo
     */
    public function prepareDump($dumpId, $additionalInfo);

    /**
     * @param string $dumpId
     * @return Dump
     */
    public function getDumpInfo($dumpId);

    /**
     * @param string $dumpId
     */
    public function destroyDump($dumpId);
}
