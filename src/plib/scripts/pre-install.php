<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

pm_Context::init('plesk-cloud-providers');

$extensions = pm_ApiCli::call('extension', ['--list']);
$extensions = array_filter(explode("\n", $extensions['stdout']));
$extensions = array_map(function($row) {
    list($id, $title) = explode(' - ', $row);
    return trim($id);
}, $extensions);

$pleskMultiServerExtensionId = 'plesk-multi-server';
if (!in_array($pleskMultiServerExtensionId, $extensions)) {
    echo "Extension '{$pleskMultiServerExtensionId}' is not installed\n";
    exit(1);
}

exit(0);
