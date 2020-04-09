<?php
require 'vendor/autoload.php';

use Aws\Ec2\Ec2Client;
// snippet-end:[ec2.php.run_instance.import]
/**
 * Run Instances
 *
 * This code expects that you have AWS credentials set up per:
 * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
 */

$provision_script = <<<SH
#!/bin/bash

echo 'Starting script' >> /home/ubuntu/provisioning.log
# install Docker
sudo apt update
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu bionic stable"
sudo apt update
apt-cache policy docker-ce
sudo apt-get install -y docker-ce docker-ce-cli containerd.io
sudo service docker start
# sudo systemctl status docker

echo 'Docker done' >> /home/ubuntu/provisioning.log

# Install docker compose
sudo curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
docker-compose --version

echo 'Docker-compose done' >> /home/ubuntu/provisioning.log

# Pull code.
mkdir /home/ubuntu/workspace
cd /home/ubuntu/workspace
git clone https://github.com/jitsi/docker-jitsi-meet.git jitsi
chown -R ubuntu:ubuntu jitsi
cd jitsi
cp env.example .env
./gen-passwords.sh
mkdir -p /home/ubuntu/.jitsi-meet-cfg/{web/letsencrypt,transcripts,prosody,jicofo,jvb,jigasi,jibri}
chown -R ubuntu:ubuntu /home/ubuntu/.jitsi*
sudo docker-compose up -d

echo 'jitsi done' >> ~/provisioning.log

SH;

// snippet-start:[ec2.php.run_instance.main]
$ec2Client = new Aws\Ec2\Ec2Client([
    'region' => 'eu-west-1',
    'version' => '2016-11-15',
    'profile' => 'default'
]);

$result = $ec2Client->runInstances(array(
    'DryRun' => false,
    // ImageId is required
    'ImageId' => 'ami-0d1f717aa2de0a9d3',
    // MinCount is required
    'MinCount' => 1,
    // MaxCount is required
    'MaxCount' => 1,
    'LaunchTemplate' => array(
//	'LaunchTemplateId' => 'lt-0b80c0ae2e0aa922f',
        'LaunchTemplateName' => 'Jitsi-instance',
    ),
    'InstanceType' => 't2.micro',
    'InstanceInitiatedShutdownBehavior' => 'terminate',
    'UserData' => base64_encode($provision_script),
));

var_dump($result);
die();
