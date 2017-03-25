# Quick start

1. Start Plesk in Docker: docker run -d -it -p 80:80 -p 443:443 -p 8880:8880 -p 8443:8443 -p 8447:8447 --name cloud-tools plesk/plesk
2. Login: https://localhost:8443/ using admin/changeme
3. Complete initial configuration
4. Go to Toos&Settings > License Management and install trial key from here: http://page.plesk.com/plesk-onyx-free-download It'll bring you Plesk Multi Server license
5. Go to Extensions > My Extensions > Upload Extension and upload the latest build of Plesk Multi Server: https://github.com/plesk/ext-plesk-cloud-providers-tools/raw/master/builds/plesk-multi-server.zip
6. Compress content of https://github.com/plesk/ext-plesk-cloud-providers-tools/tree/master/src and install the extension in Extensions > My Extensions > Upload Extension
7. Compress content of https://github.com/plesk/ext-plesk-cloud-provider-example/tree/master/src and install the extension in Extensions > My Extensions > Upload Extension
