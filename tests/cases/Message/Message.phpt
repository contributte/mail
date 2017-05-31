<?php

namespace Tests\Message;

/**
 * Test: Message\Message
 */

use Contributte\Mail\Message\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$message = new Message();
	$message->addTos([
		'foo@foo.foo' => 'Foo',
		'bar@bar.bar' => 'Bar',
	]);

	Assert::equal(['foo@foo.foo' => 'Foo', 'bar@bar.bar' => 'Bar'], $message->getHeader('To'));
});
