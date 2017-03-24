<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Provider;

class ProviderFactory
{
    /**
     * @return VpsProviderInterface[]
     */
    public static function getProviders()
    {
        static $providers;
        if (isset($providers)) {
            return $providers;
        }

        $extensions = \pm_ApiCli::call('extension', ['--list']);
        $extensions = array_filter(explode("\n", $extensions['stdout']));
        $extensions = array_map(function($row) {
            list($id, $title) = explode(' - ', $row);
            return trim($id);
        }, $extensions);
        $providers = [];
        foreach ($extensions as $extension) {
            if ($extension == \pm_Context::getModuleId()) {
                continue;
            }

            $provider = static::_getProvider($extension);
            if ($provider) {
                $providers[$extension] = $provider;
            }
        }

        return $providers;
    }

    private static function _getProvider($extension)
    {
        $parts = explode('-', $extension);
        $parts = array_map(function($part) {
            return ucfirst($part);
        }, $parts);
        $providerClass = 'Modules_' . implode('', $parts) . '_Provider';
        if (!class_exists($providerClass)) {
            return null;
        }
        if (!array_key_exists('Modules_PleskCloudProviders_Provider\\VpsProviderInterface', class_implements($providerClass))) {
            return null;
        }

        return new $providerClass;
    }
}
