<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

pm_Context::init('plesk-cloud-providers');

$request = <<<APICALL
<extension>
    <call>
        <plesk-multi-server>
            <provision-custom-deployer-register>
                <class>Modules_PleskCloudProviders_Deployer\VpsDeployer</class>
            </provision-custom-deployer-register>
        </plesk-multi-server>
    </call>
</extension>
APICALL;
$response = pm_ApiRpc::getService()->call($request);
$result = $response->extension->call->result;
if ("ok" != $result->status) {
    $errorMsg = $result->errtext;
    echo "Cannot register custom deployer: {$errorMsg}\n";
    exit(1);
}

exit(0);
