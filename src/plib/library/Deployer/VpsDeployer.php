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

        $additionalInfo = [
            'admin' => $this->_getAdminInfo($subscriptionId),
        ];
        $dumpId = $provider->deployDump($subscriptionId, $additionalInfo);

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

        $provider->prepareDump($dumpId, $additionalInfo);
        $dump = $provider->getDumpInfo($dumpId);
        \pm_Settings::set('dump-' . reset($dump->ipv4), $dumpId);

        $nodeInfo = new NodeInfo(null, $dump->ipv4, $dump->ipv6);
        $nodeInfo->setPassword($dump->password);
        return $nodeInfo;
    }

    /**
     * @param string $ipAddress
     */
    public function destroyNode($ipAddress)
    {
        $dumpId = \pm_Settings::get('dump-' . $ipAddress);

        // TODO: find provider
        $providers = ProviderFactory::getProviders();
        $provider = reset($providers);
        $provider->destroyDump($dumpId);
        \pm_Settings::set('dump-' . $ipAddress, null);
    }

    private function _getAdminInfo($subscriptionId)
    {
        if (is_numeric($subscriptionId)) {
            $domain = new \pm_Domain($subscriptionId);
            $client = $domain->getClient();
        } else {
            $client = \pm_Client::getByLogin('admin');
        }
        return [
            'company' => $client->getProperty('cname'),
            'name' => $client->getProperty('pname'),
            'phone' => $client->getProperty('phone'),
            'fax'=> $client->getProperty('fax'),
            'email' => $client->getProperty('email'),
            'address' => $client->getProperty('address'),
            'city' => $client->getProperty('city'),
            'state' => $client->getProperty('state'),
            'zip' => $client->getProperty('pcode'),
            'country'=> $client->getProperty('country'),
        ];
    }
}
