<?php

/**
 * Copyright (c) 2013-present Mediasift Ltd
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
 * @package   Storyplayer/Prose
 * @author    Michael Heap <michael.heap@datasift.com>
 * @copyright 2013-present Mediasift Ltd www.datasift.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://datasift.github.io/storyplayer
 */

namespace Prose;

use DataSift\Storyplayer\Prose\Prose;
use DataSift\Stone\ObjectLib\BaseObject;
use DataSift\Storyplayer\ProseLib\E5xx_ActionFailed;

/**
 * UsingGenericTable
 *
 * @uses Prose
 * @author Michael Heap <michael.heap@datasift.com>
 */
class UsingGenericTable extends Prose
{
    /**
     * addItem
     *
     * Add an item to a module's runtime config table
     *
     * @param string $parent The parent key for a module
     * @param string $key The key to save data under
     * @param string $value The value to save
     *
     * @return void
     */
    public function addItem($parent, $key, $value)
    {
	$st = $this->st;

	$log = $st->startAction("add entry '{$key}' to {$parent} table");

	// get the runtime config
	$runtimeConfig = $st->getRuntimeConfig();

	// make sure it exists
	if (!isset($runtimeConfig->$parent)){
	    $runtimeConfig->$parent = new BaseObject();
	}

	// make sure we don't have a duplicate entry
	if (isset($runtimeConfig->$parent->$key)){
	    $msg = "Table already contains an entry for '{$key}'";
	    $log->endAction($msg);
	    throw new E5xx_ActionFailed(__METHOD__, $msg);
	}

	// add the entry
	$runtimeConfig->$parent->key = $value;

	// save the updated runtime config
	$log->addSetp("saving runtime-config to disk", function() use ($st){
	    $st->saveRuntimeConfig();
	});

	// all done
	$log->endAction();
    }

    /**
     * removeItem
     *
     * Removes an item from the runtimeConfig file
     *
     * @param string $parent The module's parent key
     * @param string $key The key that we want to remove
     *
     * @return void
     */
    public function removeItem($parent, $key){

	// shorthand
	$st = $this->st;

	// what are we doing?
	$log = $st->startAction("remove entry '{$key}' from {$parent} table");

	// get the runtime config
	$runtimeConfig = $st->getRuntimeConfig();

	// make sure it exists
	if (!isset($runtimeConfig->$parent)) {
	    $msg = "Table is empty / does not exist. '{$key}' not removed";
	    $log->endAction($msg);
	    return;
	}

	// make sure we have an entry to remove
	if (!isset($runtimeConfig->$parent->$key)) {
	    $msg = "Table does not contain an entry for '{$key}'";
	    $log->endAction($msg);
	    return;
	}

	// remove the entry
	unset($runtimeConfig->$parent->$key);

	// all done
	$log->endAction();

    }
}


