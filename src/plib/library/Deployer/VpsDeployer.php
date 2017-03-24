<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Deployer;

use Modules_PleskMultiServer_Deployer\NodeInfo;
use Modules_PleskCloudProviders_Provider\Dump;
use Modules_PleskCloudProviders_Provider\ProviderFactory;

class VpsDeployer implements \Modules_PleskMultiServer_Deployer\DeployerInterface
{
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

        /**
         * @var Dump $dump
         */
        $dump = $provider->deployDump();
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
