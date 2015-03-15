<?php

// ========================================================================
//
// STORY DETAILS
//
// ------------------------------------------------------------------------

$story = newStoryFor('Storyplayer')
         ->inGroup(['Modules', 'Host'])
         ->called('Can start a screen session');

$story->requiresStoryplayerVersion(2);

// ========================================================================
//
// TEST SETUP / TEARDOWN
//
// ------------------------------------------------------------------------

$story->addTestSetup(function() {
	// use the checkpoint to share the name of our screen session
	$checkpoint = getCheckpoint();
	$checkpoint->session = "storyplayer_test_session";

	// make sure the session isn't running on the host
	foreach(hostWithRole('host_target') as $hostId) {
		$details = fromHost($hostId)->getScreenSessionDetails($checkpoint->session);
		if ($details) {
			usingHost($hostId)->stopProcess($details->pid);
		}
	}
});

$story->addTestTeardown(function() {
	$checkpoint = getCheckpoint();

	// if we've left the session running, go and kill it off
	foreach(hostWithRole('host_target') as $hostId) {
		$details = fromHost($hostId)->getScreenSessionDetails($checkpoint->session);
		if ($details) {
			usingHost($hostId)->stopProcess($details->pid);
		}
	}
});

// ========================================================================
//
// POSSIBLE ACTION(S)
//
// ------------------------------------------------------------------------

$story->addAction(function() {
	$checkpoint = getCheckpoint();

	foreach(hostWithRole('host_target') as $hostId) {
		usingHost($hostId)->startInScreen($checkpoint->session, "top");
	}
});

// ========================================================================
//
// POST-TEST INSPECTION
//
// ------------------------------------------------------------------------

$story->addPostTestInspection(function() {
	$checkpoint = getCheckpoint();

	foreach(hostWithRole('host_target') as $hostId) {
		expectsHost($hostId)->screenIsRunning($checkpoint->session);
	}
});