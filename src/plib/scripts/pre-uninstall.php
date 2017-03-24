<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

pm_Context::init('plesk-cloud-providers');

$request = <<<APICALL
<extension>
    <call>
        <plesk-multi-server>
            <provision-custom-deployer-reset/>
        </plesk-multi-server>
    </call>
</extension>
APICALL;
$response = pm_ApiRpc::getService()->call($request);
$result = $response->extension->call->result;
if ("ok" != $result->status) {
    $errorMsg = $result->errtext;
    echo "Cannot reset custom deployer: {$errorMsg}\n";
    exit(1);
}

exit(0);
