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
 * @package   Storyplayer/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */

namespace DataSift\Storyplayer\ConfigLib;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

use DataSift\Stone\ObjectLib\BaseObject;

/**
 * Something to find config files on disk for us
 *
 * @category  Libraries
 * @package   Storyplayer/ConfigLib
 * @author    Stuart Herbert <stuart.herbert@datasift.com>
 * @copyright 2011-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/stone
 */
class ConfigFinder
{
    /**
     * the regex that describes the config file(s) we are looking for
     *
     * @var string
     */
    protected $pattern;

    /**
     * our constructor
     *
     * create instances of this class, to pass into the ConfigList for
     * finding files
     *
     * @param string $pattern
     *        the regex that describes the config file(s) that we are
     *        looking for
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * find a list of config files in a folder
     *
     * @param  string $directory
     *         the folder to search
     * @return array
     */
    public function getListOfConfigFilesIn($directory)
    {
        // does the directory exist?
        if (!is_dir($directory)) {
            return [];
        }

        // use the SPL to do the heavy lifting
        $dirIter = new RecursiveDirectoryIterator($directory);
        $recIter = new RecursiveIteratorIterator($dirIter);
        $regIter = new RegexIterator($recIter, '|^.+' . $this->pattern . '$|i', RegexIterator::GET_MATCH);

        // what happened?
        $filenames = [];
        foreach ($regIter as $match) {
            $filenames[] = $match[0];
        }

        // let's get the list into some semblance of order
        sort($filenames);

        // all done
        return $filenames;
    }
}