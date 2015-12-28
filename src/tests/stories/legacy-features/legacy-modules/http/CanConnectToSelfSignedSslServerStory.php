<?php

// ========================================================================
//
// STORY DETAILS
//
// ------------------------------------------------------------------------

$story = newStoryFor('Storyplayer')
         ->inGroup(['Modules', 'HTTP'])
         ->called('Can connect to self-signed SSL server');

$story->requiresStoryplayerVersion(2);

// ========================================================================
//
// POSSIBLE ACTION(S)
//
// ------------------------------------------------------------------------

$story->addAction(function() {
    $checkpoint = getCheckpoint();
    $checkpoint->responses = [];

    foreach (hostWithRole('ssl_target') as $hostname) {
        $url = "https://" . fromHost($hostname)->getHostname();
        $checkpoint->responses[] = fromHttp()->get($url);
    }
});

// ========================================================================
//
// POST-TEST INSPECTION
//
// ------------------------------------------------------------------------

$story->addPostTestInspection(function($st) {
    $checkpoint = getCheckpoint();
    assertsObject($checkpoint)->hasAttribute("responses");
    assertsArray($checkpoint->responses)->isExpectedType();

    foreach ($checkpoint->responses as $response) {
        expectsHttpResponse($response)->hasStatusCode(200);
    }
});