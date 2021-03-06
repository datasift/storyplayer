<?php

use Storyplayer\SPv3\Modules\Asserts;
use Storyplayer\SPv3\Modules\Checkpoint;
use Storyplayer\SPv3\Modules\Log;
use Storyplayer\SPv3\Stories\BuildStory;
use StoryplayerInternals\SPv3\Modules\RuntimeTable;

// ========================================================================
//
// STORY DETAILS
//
// ------------------------------------------------------------------------

$story = BuildStory::newStory();

// this format suits a test that focuses on checking for a specific consequence
// of an action (e.g. testing robustness or correctness)
$story->setScenario([
    "given:",
    "- an existing runtime table",
    "using the RuntimeTable module",
    "if I attempt to get a key from a group that does not exist inside that table",
    "- I get NULL",
    "afterwards:",
    "- the group will not exist inside that table",
]);

// ========================================================================
//
// TEST SETUP / TEAR-DOWN
//
// ------------------------------------------------------------------------

$story->addTestSetup(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("create our test table");

    RuntimeTable::fromRuntimeTable('functional-tests')->getTable();
    RuntimeTable::expectsRuntimeTable('functional-tests')->exists();

    // all done
    $log->endAction();
});

$story->addTestTeardown(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("remove our test table");

    // undo anything that you did in addTestSetup()
    RuntimeTable::usingRuntimeTable('functional-tests')->removeTable();

    // all done
    $log->endAction();
});

// ========================================================================
//
// ACTIONS
//
// ------------------------------------------------------------------------

$story->addAction(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("make sure NULL is returned when getting key from non-existing group table");

    // this is where you perform the steps of your user story
    $data = RuntimeTable::fromRuntimeTable('functional-tests')->getItemFromGroup('does-not-exist', 'cannot-exist');
    Asserts::assertsNull($data)->isNull();

    // all done
    $log->endAction();
});

// ========================================================================
//
// POST-TEST INSPECTION
//
// ------------------------------------------------------------------------

$story->addPostTestInspection(function() {
    // what are we doing?
    $log = Log::usingLog()->startAction("does the group still not exist?");

    $tables = RuntimeTable::fromRuntimeTables()->getAllTables();
    Asserts::assertsObject($tables)->hasAttribute('functional-tests');
    Asserts::assertsObject($tables->{'functional-tests'})->doesNotHaveAttribute('does-not-exist');

    // all done
    $log->endAction();
});
