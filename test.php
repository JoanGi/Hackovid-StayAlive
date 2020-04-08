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
    'region' => 'us-west-2',
    'version' => '2016-11-15',
    'profile' => 'default'
]);

$result = $ec2Client->runInstances(array(
    'DryRun' => false,
    // ImageId is required
    'ImageId' => 'string',
    // MinCount is required
    'MinCount' => 1,
    // MaxCount is required
    'MaxCount' => 1,
));

var_dump($result);

