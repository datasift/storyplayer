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
 * @package   Storyplayer/Cli
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace DataSift\Storyplayer\Cli;

use Exception;
use stdClass;
use Phix_Project\CliEngine;
use Phix_Project\CliEngine\CliCommand;
use Phix_Project\ExceptionsLib1\Legacy_ErrorHandler;
use Phix_Project\ExceptionsLib1\Legacy_ErrorException;
use DataSift\Stone\ConfigLib\E5xx_ConfigFileNotFound;
use DataSift\Stone\ConfigLib\E5xx_InvalidConfigFile;
use DataSift\Stone\ObjectLib\BaseObject;
use DataSift\Storyplayer\PlayerLib\E4xx_NoSuchReport;
use DataSift\Storyplayer\Console\DevModeConsole;
use DataSift\Storyplayer\PlayerLib\StoryTeller;
use DataSift\Storyplayer\Injectables;
use Storyplayer\SPv2\Modules\Users;

/**
 * Support for loading / saving test user data from/to a config file
 *
 * @category  Libraries
 * @package   Storyplayer/Cli
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class Feature_TestUsersSupport implements Feature
{
    public function addSwitches(CliCommand $command, $injectables)
    {
        $command->addSwitches([
            new Feature_TestUsersSwitch,
            new Feature_ReadOnlyTestUsersSwitch
        ]);
    }

    public function initBeforeModulesAvailable(CliEngine $engine, CliCommand $command, Injectables $injectables)
    {
        // no-op
    }

    public function initAfterModulesAvailable(StoryTeller $st, CliEngine $engine, Injectables $injectables)
    {

        // make this switch backwards compatible. If no test users file is specified,
        // generate a temporary file
        if (strlen($engine->options->testUsersFile) < 1) {
            $engine->options->testUsersFile = tempnam(sys_get_temp_dir(), 'StoryplayerUsersFile_');
        }

        // shorthand
        $output = $injectables->output;

        // do we *have* any users to load?
        if (!isset($engine->options->testUsersFile)) {
            // do nothing
            return;
        }

        // understand what we are about to do
        $filename = $engine->options->testUsersFile;
        $isReadOnly = false;
        if (isset($engine->options->readOnlyTestUsers) && $engine->options->readOnlyTestUsers) {
            $isReadOnly = true;
        }

        // go silent
        $output->setSilentMode();

        // load the users
        $this->loadTestFile($st, $output, $filename, $isReadOnly);

        // are we read-only?
        if (isset($engine->options->readOnlyTestUsers) && $engine->options->readOnlyTestUsers) {
            Users::usingUsers()->setUsersFileIsReadOnly();
        }
        else
        {
            $setting = fromTestEnvironment()->getModuleSetting("users.readOnly");
            if ($setting === true) {
                Users::usingUsers()->setUsersFileIsReadOnly();
            }
        }

        // all done
        $output->resetSilentMode();
    }

    private function loadTestFile($st, $output, $filename, $isReadOnly)
    {
        // special case - file does not exist
        if (!file_exists($filename)) {
            $output->logCliWarning("test users file '{$filename}' not found");

            // special case
            if ($isReadOnly) {
                $output->logCliWarning("--read-only-users used; Storyplayer will NOT create this file on exit");
                return;
            }

            // general case
            $output->logCliWarning("Storyplayer will create this file when it exits");
            $st->setTestUsersFilename($filename);
            return;
        }

        // special case - file is empty
        if (filesize($filename) == 0) {
            $output->logCliWarning("test users file '{$filename}' is empty");
            $st->setTestUsersFilename($filename);
            return;
        }

        // if we get here, we want to try and load the file
        try {
            Users::usingUsers()->loadUsersFromFile($filename);
            $st->setTestUsersFilename($filename);
        }
        catch (Exception $e) {
            $output->logCliErrorWithException("could not load test users file '{$filename}'", $e);
            exit(1);
        }
    }
}
