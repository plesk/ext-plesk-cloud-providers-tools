<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Deployer;

use Modules_PleskMultiServer_Deployer\NodeInfo;
use Modules_PleskCloudProviders_Provider\Dump;
use Modules_PleskCloudProviders_Provider\ProviderFactory;

class VpsDeployer implements \Modules_PleskMultiServer_Deployer\DeployerInterface
{
    const POLLING_INTERVAL = 20;
    const TIMEOPUT = 600;
    /**
     * Returns deployed VPS info
     *
     * @param int $subscriptionId
     * @return NodeInfo
     * @throws \pm_Exception
     */
    public function deployNode($subscriptionId)
    {
        $providers = ProviderFactory::getProviders();
        $provider = reset($providers);
        if (is_null($provider)) {
            throw new \pm_Exception('Provider not found');
        }

        $dumpId = $provider->deployDump($subscriptionId);

        \pm_Log::info("Waiting till dump {$dumpId} is deployed");
        $attempt = 0;
        while (true) {
            if ($provider->isDumpDeployed($dumpId)) {
                break;
            }
            $attempt++;
            \pm_Log::debug("Dump with id '{$dumpId}' is not deployed yet. Attempt: {$attempt}. Sleep for {$timeout} sec...");
            if ($attempt * static::POLLING_INTERVAL > static::TIMEOPUT) {
                throw new \pm_Exception('Dumps is not deployed because of timeout');
            }
            sleep(static::POLLING_INTERVAL);
        }

        $provider->prepareDump($dumpId);
        $dump = $provider->getDumpInfo($dumpId);

        $nodeInfo = new NodeInfo(null, $dump->ipv4, $dump->ipv6);
        $nodeInfo->setPassword($dump->password);
        return $nodeInfo;
    }

    /**
     * @param string $ipAddress
     */
    public function destroyNode($ipAddress)
    {
        // TODO: find provider
        $providers = ProviderFactory::getProviders();
        $provider = reset($providers);
        $dump = new Dump();
        if (false !== strpos($ipAddress, ':')) {
            $dump->ipv6 = [$ipAddress];
        } else {
            $dump->ipv4 = [$ipAddress];
        }
        $provider->destroyDump($dump);
    }
}
