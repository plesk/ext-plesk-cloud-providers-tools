<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Provider;

interface VpsProviderInterface
{
    /**
     * @param int $subscriptionId
     * @return string $dumpId
     */
    public function deployDump($subscriptionId);

    /**
     * @param string $dumpId
     * @return bool
     */
    public function isDumpDeployed($dumpId);

    /**
     * @param string $dumpId
     */
    public function prepareDump($dumpId);

    /**
     * @param string $dumpId
     * @return Dump
     */
    public function getDumpInfo($dumpId);

    /**
     * @param Dump
     */
    public function destroyDump(Dump $dump);
}
