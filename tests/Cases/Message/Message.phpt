<?php declare(strict_types = 1);

namespace Tests\Cases\Message;

use Contributte\Mail\Message\Message;
use Contributte\Tester\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$message = new Message();
	$message->addTos([
		'foo@foo.foo' => 'Foo',
		'bar@bar.bar' => 'Bar',
	]);

	Assert::equal(['foo@foo.foo' => 'Foo', 'bar@bar.bar' => 'Bar'], $message->getHeader('To'));
});
