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

use Phix_Project\Injectables as BaseInjectables;

/**
 * a container for common services and data, to avoid making them global
 *
 * @category  Libraries
 * @package   Storyplayer/Cli
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */
class Injectables extends BaseInjectables
{
	use Injectables_ActiveConfigSupport;
	use Injectables_ActiveDeviceSupport;
	use Injectables_ActiveLocalEnvironmentConfigSupport;
	use Injectables_ActiveSystemUnderTestConfigSupport;
	use Injectables_ActiveTestEnvironmentConfigSupport;
	use Injectables_AdditionalConfigsSupport;
	use Injectables_DefaultConfigSupport;
	use Injectables_DefaultLocalEnvironmentName;
	use Injectables_DefaultSystemUnderTestName;
	use Injectables_DefaultTestEnvironmentName;
	use Injectables_KnownDevicesSupport;
	use Injectables_KnownLocalEnvironmentsSupport;
	use Injectables_KnownSystemsUnderTestSupport;
	use Injectables_KnownTestEnvironmentsSupport;
	use Injectables_OutputSupport;
	use Injectables_PhaseLoaderSupport;
	use Injectables_ProseLoaderSupport;
	use Injectables_ReportLoaderSupport;
	use Injectables_RuntimeConfigSupport;
	use Injectables_StaticConfigManagerSupport;
	use Injectables_StoryplayerConfigFilenameSupport;
	use Injectables_StoryplayerConfigSupport;
	use Injectables_TemplateEngineSupport;
}