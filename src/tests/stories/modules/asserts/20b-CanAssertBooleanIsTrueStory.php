<?php

// ========================================================================
//
// STORY DETAILS
//
// ------------------------------------------------------------------------

$story = newStoryFor('Storyplayer')
         ->inGroup(['Modules', 'AssertsBoolean'])
         ->called('Can check that a boolean is true');

$story->requiresStoryplayerVersion(2);

// ========================================================================
//
// STORY SETUP / TEAR-DOWN
//
// ------------------------------------------------------------------------

// ========================================================================
//
// POSSIBLE ACTION(S)
//
// ------------------------------------------------------------------------

$story->addAction(function() {
	$checkpoint = getCheckpoint();
	$checkpoint->test2Passed = false;

	// this should pass
	$goodData1 = true;
	assertsBoolean($goodData1)->isTrue();

	// these should all fail
	$badData1 = false;
	try {
		assertsBoolean($badData1)->isTrue();
	}
	catch (Exception $e) {
		$checkpoint->test2Passed = true;
	}
});

// ========================================================================
//
// POST-TEST INSPECTION
//
// ------------------------------------------------------------------------

$story->addPostTestInspection(function() {
	$checkpoint = getCheckpoint();

	assertsObject($checkpoint)->hasAttribute('test2Passed');
	assertsBoolean($checkpoint->test2Passed)->isTrue();

});