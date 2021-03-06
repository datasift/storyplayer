<?php

use Storyplayer\SPv3\Modules\Asserts;
use Storyplayer\SPv3\Modules\Checkpoint;
use Storyplayer\SPv3\Modules\Failure;
use Storyplayer\SPv3\Modules\Log;
use Storyplayer\SPv3\Stories\BuildStory;

// ========================================================================
//
// STORY DETAILS
//
// ------------------------------------------------------------------------

$story = BuildStory::newStory();

// ========================================================================
//
// ACTIONS
//
// ------------------------------------------------------------------------

$story->addAction(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("cannot assert empty string is NULL");

    // this is where you perform the steps of your user story
    Failure::expectsFailure()->when("assertion", function() {
        Asserts::assertsNull('')->isNull();
    });

    // all done
    $log->endAction();
});

$story->addAction(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("cannot assert 'hello, world' is NULL");

    // this is where you perform the steps of your user story
    Failure::expectsFailure()->when("assertion", function() {
        Asserts::assertsNull('hello, world')->isNull();
    });

    // all done
    $log->endAction();
});
