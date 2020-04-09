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

// snippet-start:[ec2.php.run_instance.main]
$ec2Client = new Aws\Ec2\Ec2Client([
    'region' => 'eu-west-1',
    'version' => '2016-11-15',
    'profile' => 'default'
]);

$instanceId='i-06fbe07c737b287fc';

$ec2Client->stopInstances(array('Force' => true, 'InstanceIds' => array($instanceId)));
$ec2Client->waitUntil('InstanceStopped', array('InstanceIds' => array($instanceId)));
print("Stopped\n");

die();
