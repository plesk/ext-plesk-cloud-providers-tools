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

    private $_password;
    /**
     * Returns deployed VPS info
     *
     * @param int $subscriptionId
     * @return NodeInfo
     * @throws \pm_Exception
     */
    public function deployNode($subscriptionId, $password)
    {
        try {
            $this->_password = $password;
            $dumpId = \pm_Settings::get("subscription-{$subscriptionId}");

            $providers = ProviderFactory::getProviders();
            $provider = reset($providers);
            if (is_null($provider)) {
                throw new \pm_Exception('Provider not found');
            }
            $providerExtensionId = reset(array_keys($providers));
            \pm_Context::init($providerExtensionId);

            $additionalInfo = [
                'admin' => $this->_getAdminInfo($subscriptionId),
            ];
            if (!$dumpId) {
                $dumpId = $provider->deployDump($subscriptionId, $additionalInfo);
            }

            \pm_Log::info("Waiting till dump {$dumpId} is deployed");
            $attempt = 0;
            while (true) {
                if ($provider->isDumpDeployed($dumpId)) {
                    break;
                }
                $attempt++;
                \pm_Log::debug("Dump with id '{$dumpId}' is not deployed yet. Attempt: {$attempt}. Sleep for " . static::POLLING_INTERVAL . " sec...");
                if ($attempt * static::POLLING_INTERVAL > static::TIMEOPUT) {
                    throw new \pm_Exception('Dumps is not deployed because of timeout');
                }
                sleep(static::POLLING_INTERVAL);
            }

            $provider->prepareDump($dumpId, $additionalInfo);
            $dump = $provider->getDumpInfo($dumpId);
            \pm_Settings::set("dump-{$dump->ipv4}", $dumpId);
            \pm_Settings::set("subscription-{$subscriptionId}", $dumpId);
            \pm_Settings::set("dump-{$dumpId}-info", json_encode([
                'ipv4' => $dump->ipv4,
                'ipv6' => $dump->ipv6,
                'password' => $dump->password,
                'subscription' => $subscriptionId,
            ]));

            $ipv4 = !empty($dump->ipv4) ? [$dump->ipv4] : [];
            $ipv6 = !empty($dump->ipv6) ? [$dump->ipv6] : [];
            $nodeInfo = new NodeInfo(null, $ipv4, $ipv6);
            $nodeInfo->setPassword($dump->password);

            \pm_Context::init('plesk-multi-server');

            return $nodeInfo;
        } catch (\Exception $e) {
            \pm_Context::init('plesk-multi-server');
            throw $e;
        }
    }

    /**
     * @param string $ipAddress
     */
    public function destroyNode($ipAddress)
    {
        $dumpId = \pm_Settings::get("dump-{$ipAddress}");

        // TODO: find provider
        $providers = ProviderFactory::getProviders();
        $provider = reset($providers);
        $provider->destroyDump($dumpId);
        \pm_Settings::set("dump-{$ipAddress}", null);
        $info = json_decode(\pm_Settings::get("dump-{$dumpId}-info"), true);
        \pm_Settings::set("dump-{$dumpId}-info", null);
        \pm_Settings::set("subscription-{$info['subscription']}", null);
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
            'passwd' => $this->_password,
        ];
    }
}
