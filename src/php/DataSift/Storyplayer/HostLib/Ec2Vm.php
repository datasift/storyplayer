<?php

/**
 * Copyright (c) 2011-present Mediasift Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   Storyplayer/HostLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\HostLib;

use Exception;

use DataSift\Storyplayer\CommandLib\CommandResult;
use DataSift\Storyplayer\OsLib;
use DataSift\Storyplayer\PlayerLib\StoryTeller;
use DataSift\Stone\ObjectLib\BaseObject;
use Storyplayer\SPv2\Modules\Exceptions;
use Storyplayer\SPv2\Modules\Log;

/**
 * the things you can do / learn about EC2 virtual machine
 *
 * @category  Libraries
 * @package   Storyplayer/HostLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class Ec2Vm implements SupportedHost
{
    /**
     *
     * @var StoryTeller
     */
    protected $st;

    /**
     *
     * @param StoryTeller $st
     */
    public function __construct(StoryTeller $st)
    {
        // remember
        $this->st = $st;
    }

    /**
     *
     * @param  Ev2VmDetails $vmDetails
     * @param  array        $provisioningVars
     * @return void
     */
    public function createHost($vmDetails, $provisioningVars = array())
    {
        // what are we doing?
        $log =Log::usingLog()->startAction('provision new VM');

        // make sure we like the provided details
        foreach(array('name', 'environment', 'osName', 'amiId', 'securityGroup') as $param) {
            if (!isset($vmDetails->$param)) {
                throw Exceptions::newActionFailedException(__METHOD__, "missing vmDetails['{$param}']");
            }
        }

        // because EC2 is a shared resource, our VMs need namespacing
        $vmDetails->ec2Name = $vmDetails->environment . '.' . $vmDetails->hostId;

        // get our Ec2 client from the SDK
        $client = fromAws()->getEc2Client();

        // make sure the VM is stopped, if it is running
        $log->addStep("stop EC2 VM '{$vmDetails->ec2Name}' if already running", function() use($vmDetails, $client) {
            if (fromEc2Instance($vmDetails->hostId)->getInstanceisRunning()) {
                // stop the host
                usingEc2()->destroyVm($vmDetails->hostId);
            }
        });

        // remove any existing hosts table entry
        usingHostsTable()->removeHost($vmDetails->hostId);

        // let's start the VM
        $response = null;
        try {
            $log->addStep("create EC2 VM using AMI '{$vmDetails->amiId}'", function() use($client, $vmDetails, &$response) {
                $response = $client->runInstances(array(
                    'ImageId' => $vmDetails->amiId,
                    'MinCount' => 1,
                    'MaxCount' => 1,
                    'KeyName' => $vmDetails->keyPairName,
                    'InstanceType' => $vmDetails->instanceType,
                ));

                // we need to name this instance
                if (isset($response['Instances'], $response['Instances'][0], $response['Instances'][0]['InstanceId'])) {
                    $client->createTags(array(
                        'Resources' => array($response['Instances'][0]['InstanceId']),
                        'Tags' => array(
                            array (
                                'Key' => 'Name',
                                'Value' => $vmDetails->ec2Name
                            )
                        )
                    ));
                }
            });
        }
        catch (Exception $e)
        {
            // something went wrong
            $log->endAction("VM failed to provision :(");
            throw Exceptions::newActionFailedException(__METHOD__, $e->getMessage());
        }

        // we'll need this for future API calls
        $instanceId = $response['Instances'][0]['InstanceId'];

        try {
            // now, we need to wait until this instance is running
            $log->addStep("wait for EC2 VM '{$instanceId}' to finish booting", function() use($client, $vmDetails, $response, $instanceId) {
                $client->waitUntilInstanceRunning(array(
                    'InstanceIds' => array($instanceId),
                    'waiter.interval'     => 10,
                    'waiter.max_attempts' => 10
                ));

                // remember the instance data, to save us time in the future
                $vmDetails->ec2Instance = $response['Instances'][0];
            });
        }
        catch (Exception $e)
        {
            // something went wrong
            $log->endAction("VM failed to start :(");
            throw Exceptions::newActionFailedException(__METHOD__, $e->getMessage());
        }

        // yes it did!!
        //
        // remember this vm, now that it is running
        usingHostsTable()->addHost($vmDetails->hostId, $vmDetails);

        // now, we need its IP address
        $ipAddress = $this->determineIpAddress($vmDetails);

        // store the IP address for future use
        $vmDetails->ipAddress = $ipAddress;

        // mark the box as provisioned
        // we will use this in stopBox() to avoid destroying VMs that failed
        // to provision
        $vmDetails->provisioned = true;

        // all done
        $log->endAction("VM successfully started; IP address is {$ipAddress}");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return void
     */
    public function startHost($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("start VM");

        // is the VM actually running?
        if ($this->isRunning($vmDetails)) {
            // yes it is ... nothing to do
            //
            // we've decided not to treat this as an error ... that might
            // change in a future release
            $log->endAction("VM is already running");
            return;
        }

        // get our Ec2 client from the SDK
        $client = fromAws()->getEc2Client();

        // what is our instanceId?
        $instanceId = $vmDetails->ec2Instance['InstanceId'];

        // let's start the VM
        try {
            $log->addStep("start EC2 VM instance '{$instanceId}'", function() use($client, &$response, $instanceId) {
                $response = $client->startInstances(array(
                    "InstanceIds" => array($instanceId)
                ));
            });

            // now, we need to wait until this instance is running
            $log->addStep("wait for EC2 VM '{$instanceId}' to finish booting", function() use($client, $vmDetails, $response, $instanceId) {
                $client->waitUntilInstanceRunning(array(
                    'InstanceIds' => array($instanceId),
                    'waiter.interval'     => 10,
                    'waiter.max_attempts' => 10
                ));

                // remember the instance data, to save us time in the future
                $vmDetails->ec2Instance = $response['Instances'][0];
            });
        }
        catch (Exception $e)
        {
            // something went wrong
            $log->endAction("VM failed to start :(");
            throw Exceptions::newActionFailedException(__METHOD__, $e->getMessage());
        }

        // yes it did!!
        //
        // now, we need its IP address, which may have changed
        $ipAddress = $this->determineIpAddress($vmDetails);

        // store the IP address for future use
        $vmDetails->ipAddress = $ipAddress;

        // all done
        $log->endAction("VM successfully started; IP address is {$ipAddress}");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return void
     */
    public function stopHost($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("stop VM");

        // is the VM actually running?
        if (!$this->isRunning($vmDetails)) {
            // we've decided not to treat this as an error ... that might
            // change in a future release
            $log->endAction("VM was already stopped or destroyed");
            return;
        }

        // get our Ec2 client from the SDK
        $client = fromAws()->getEc2Client();

        // what is our instanceId?
        $instanceId = $vmDetails->ec2Instance['InstanceId'];

        // let's stop the VM
        try {
            $log->addStep("stop EC2 VM instance '{$instanceId}'", function() use($client, &$response, $instanceId) {
                $response = $client->stopInstances(array(
                    "InstanceIds" => array($instanceId)
                ));
            });

            // now, we need to wait until this instance has stopped
            $log->addStep("wait for EC2 VM '{$instanceId}' to finish shutting down", function() use($client, $vmDetails, $response, $instanceId) {
                $client->waitUntilInstanceStopped(array(
                    'InstanceIds' => array($instanceId),
                    'waiter.interval'     => 10,
                    'waiter.max_attempts' => 18
                ));

                // remember the instance data, to save us time in the future
                $vmDetails->ec2Instance = $response['Instances'][0];
            });
        }
        catch (Exception $e)
        {
            // something went wrong
            $log->endAction("VM failed to stop :(");
            throw Exceptions::newActionFailedException(__METHOD__, $e->getMessage());
        }

        // all done - success!
        $log->endAction("VM successfully stopped");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return void
     */
    public function restartHost($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("restart VM");

        // stop and start
        $this->stopHost($vmDetails);
        $this->startHost($vmDetails);

        // all done
        $log->endAction("VM successfully restarted");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return void
     */
    public function powerOffHost($vmDetails)
    {
        // sadly, not supported by EC2
        //
        // for now, we make this an alias of stopHost().
        return $this->stopHost($vmDetails);
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return void
     */
    public function destroyHost($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("destroy VM");

        // get our Ec2 client from the SDK
        $client = fromAws()->getEc2Client();

        // what is our instanceId?
        $instanceId = $vmDetails->ec2Instance['InstanceId'];

        // let's destroy the VM
        try {
            $log->addStep("destroy EC2 VM instance '{$instanceId}'", function() use($client, &$response, $instanceId) {
                $response = $client->terminateInstances(array(
                    "InstanceIds" => array($instanceId)
                ));
            });

            // now, we need to wait until this instance has been terminated
            $log->addStep("wait for EC2 VM '{$instanceId}' to finish terminating", function() use($client, $vmDetails, $response, $instanceId) {
                $client->waitUntilInstanceTerminated(array(
                    'InstanceIds' => array($instanceId),
                    'waiter.interval'     => 10,
                    'waiter.max_attempts' => 10
                ));
            });
        }
        catch (Exception $e)
        {
            // something went wrong
            $log->endAction("VM failed to terminate :(");
            throw Exceptions::newActionFailedException(__METHOD__, $e->getMessage());
        }

        // if we get here, we need to forget about this VM
        usingHostsTable()->removeHost($vmDetails->hostId);

        // all done
        $log->endAction();
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @param  string $command
     * @return void
     */
    public function runCommandAgainstHostManager($vmDetails, $command)
    {
        throw Exceptions::newActionFailedException(__METHOD__, "not supported on EC2");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @param  string $command
     * @return void
     */
    public function runCommandViaHostManager($vmDetails, $command)
    {
        throw Exceptions::newActionFailedException(__METHOD__, "not supported on EC2");
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return boolean
     */
    public function isRunning($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("determine status of EC2 VM '{$vmDetails->hostId}'");

        // get the instance data
        $instance = fromEc2()->getInstance($vmDetails->hostId);
        if (!$instance) {
            $log->endAction("no such instance");
            return false;
        }
        $isRunning = fromEc2InstanceIsRunning($vmDetails->ec2Name)->getInstanceIsRunning();
        if (!$isRunning) {
            $log->endAction("VM is not running");
            return false;
        }

        // all done
        $log->endAction("VM is running");
        return true;
    }

    /**
     *
     * @param  Ec2VmDetails $vmDetails
     * @return string
     */
    public function determineIpAddress($vmDetails)
    {
        // what are we doing?
        $log =Log::usingLog()->startAction("determine IP address of EC2 VM '{$vmDetails->hostId}'");

        // we need to get a fresh copy of the instance details
        $dnsName = fromEc2Instance($vmDetails->hostId)->getPublicDnsName();

        // do we have a DNS name?
        if (!$dnsName) {
            throw Exceptions::newActionFailedException(__METHOD__, "Ec2Vm has no public DNS name - is the VM broken?");
        }

        // convert it to an IP address
        $ipAddress = gethostbyname($dnsName);

        // did we get one?
        if ($ipAddress == $dnsName) {
            throw Exceptions::newActionFailedException(__METHOD__, "unable to convert hostname '{$dnsName}' into an IP address");
        }

        // all done
        $log->endAction("IP address is '{$ipAddress}'");
        return $ipAddress;
    }
}
