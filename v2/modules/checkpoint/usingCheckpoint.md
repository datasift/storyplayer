---
layout: v2/modules-checkpoint
title: usingCheckpoint()
prev: '<a href="../../modules/checkpoint/fromCheckpoint.html">Prev: fromCheckpoint()</a>'
next: '<a href="../../modules/curl/index.html">Next: The cURL Module</a>'
updated_for_v2: true
---

# usingCheckpoint()

_usingCheckpoint()_ allows you to put data into the checkpoint without having to call `getCheckpoint()` yourself.

The source code for these actions can be found in the class `Prose\UsingCheckpoint`.

<div class="callout warning" markdown="1">
#### Deprecated in Storyplayer v2

The functionality documented here is deprecated, and will be removed in Storyplayer v3.

Use [getCheckpoint()](getCheckpoint.html) instead.
</div>

## Behaviour And Return Codes

Every action makes changes to the inter-phase checkpoint.

* If the action succeeds, the action returns control to your code, and does not return a value.
* If the action fails, an exception is throw. _Do not catch exceptions thrown by these actions._ Let them go through to Storyplayer, which will use the information to work out whether your story as a whole passes or fails.

Write your story as if every action will be successful.

## set()

Use `usingCheckpoint()->set()` to store data in the checkpoint.

{% highlight php startinline %}
usingCheckpoint()->set('balance', 10);
{% endhighlight %}

This is the same as doing:

{% highlight php startinline %}
$checkpoint = getCheckpoint();
$checkpoint->balance = 10;
{% endhighlight %}

Which way you use is down to personal preference.