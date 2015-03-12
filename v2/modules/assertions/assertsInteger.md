---
layout: v2/modules-assertions
title: Integer Assertions
prev: '<a href="../../modules/assertions/assertsDouble.html">Prev: Double Assertions</a>'
next: '<a href="../../modules/assertions/assertsObject.html">Next: Object Assertions</a>'
updated_for_v2: true
---

# Integer Assertions

_assertsInteger()_ allows you to test a PHP integer, and to compare it against another PHP integer.

The source code for these actions can be found in the class `Prose\AssertsInteger`.

## doesNotEqual()

Use `assertsInteger()->doesNotEqual()` to make sure that two integer numbers are not the same.

{% highlight php startinline %}
$expected = 1;
$actual   = 2;
assertsInteger($actual)->doesNotEqual($expected);
{% endhighlight %}

See _[equals()](#equals)_ for a discussion of how this test works.

## equals()

Use `assertsInteger()->equals()` to make sure that two integer numbers are the same.

{% highlight php startinline %}
$expected = 1;
$actual   = 1;
assertsInteger($actual)->equals($expected);
{% endhighlight %}

If the test fails, Storyplayer's output will show the differences between the two numbers.

## isEmpty()

Use `assertsInteger()->isEmpty()` to make sure that a variable is empty.

{% highlight php startinline %}
$data = 0;
assertsInteger($data)->isEmpty();
{% endhighlight %}

## isGreaterThan()

Use `assertsInteger()->isGreaterThan()` to make sure that an integer number is larger than a value you provide.

{% highlight php startinline %}
$data = 2;
assertsInteger($data)->isGreaterThan(1);
{% endhighlight %}

## isGreaterThanOrEqualTo()

Use `assertsInteger()->isGreaterThan()` to make sure that an integer number is at least a value you provide.

{% highlight php startinline %}
$data = 2;
assertsInteger($data)->isGreaterThanOrEqualTo(1);
{% endhighlight %}

## isInteger()

Use `assertsInteger()->isInteger()` to make sure that something really is an integer.

{% highlight php startinline %}
$data = 1.1;
assertsInteger($data)->isInteger();
{% endhighlight %}

This is most often used in the [post-test inspection phase](../../stories/post-test-inspection.html) to validate the data in the [checkpoint](../../stories/the-checkpoint.html):

{% highlight php startinline %}
$story->addAction(function() {
    // get the checkpoint
    $checkpoint = getCheckpoint();

    // remember the quantity for latest checking
    $checkpoint->quantity = 5;
});

$story->addPostTestInspection(function() {
    // get the checkpoint
    $checkpoint = getCheckpoint();

    // make sure the checkpoint contains
    // the final quantity
    assertsObject($checkpoint)->hasAttribute("quantity");
    assertsInteger($checkpoint->quantity)->isInteger();
});
{% endhighlight %}

## isLessThan()

Use `assertsInteger()->isLessThan()` to make sure that an integer number is smaller than a value you provide.

{% highlight php startinline %}
$data = 1;
assertsInteger($data)->isLessThan(2);
{% endhighlight %}

## isLessThanOrEqualTo()

Use `assertsInteger()->isLessThanOrEqualTo()` to make sure that an integer number is no larger than a value you provide.

{% highlight php startinline %}
$data = 1;
assertsInteger($data)->isLessThanOrEqualTo(1);
{% endhighlight %}

## isNotEmpty()

Use `assertsInteger()->isNotEmpty()` to make sure that an integer number is not empty.

{% highlight php startinline %}
$data = 1;
assertsInteger($data)->isNotEmpty();
{% endhighlight %}

## isNull()

Use `assertsInteger()->isNull()` to make sure that the PHP variable is actually NULL, rather than an integer number.

{% highlight php startinline %}
$data = null;
assertsInteger($data)->isNull()
{% endhighlight %}

This has been added for completeness; we'd always recommend using _[isInteger()](#isinteger)_ instead of testing for NULL.

## isNotNull()

Use `assertsInteger()->isNotNull()` to make sure that the PHP variable is not NULL.

{% highlight php startinline %}
$data = 1;
assertsInteger($data)->isNotNull();
{% endhighlight %}

This has been added for completeness; we'd always recommend using _[isInteger()](#isinteger)_ instead of testing for NULL.