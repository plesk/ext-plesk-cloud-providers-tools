<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

namespace Modules_PleskCloudProviders_Provider;

interface VpsProviderInterface
{
    /**
     * @return Dump
     */
    public function deployDump();

    /**
     * @param Dump
     */
    public function destroyDump(Dump $dump);
}
