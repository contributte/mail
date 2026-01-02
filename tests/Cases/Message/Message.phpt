<?php declare(strict_types = 1);

namespace Tests\Cases\Message;

use Contributte\Mail\Message\Message;
use Contributte\Tester\Toolkit;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test addTos with multiple recipients
Toolkit::test(function (): void {
	$message = new Message();
	$message->addTos([
		'foo@foo.foo' => 'Foo',
		'bar@bar.bar' => 'Bar',
	]);

	Assert::equal(['foo@foo.foo' => 'Foo', 'bar@bar.bar' => 'Bar'], $message->getHeader('To'));
});

// Test addTos with null name
Toolkit::test(function (): void {
	$message = new Message();
	$message->addTos([
		'foo@foo.foo' => null,
		'bar@bar.bar' => 'Bar',
	]);

	Assert::equal(['foo@foo.foo' => null, 'bar@bar.bar' => 'Bar'], $message->getHeader('To'));
});

// Test addTos with empty array
Toolkit::test(function (): void {
	$message = new Message();
	$message->addTos([]);

	Assert::null($message->getHeader('To'));
});

// Test addTos combines with existing To recipients
Toolkit::test(function (): void {
	$message = new Message();
	$message->addTo('existing@test.com', 'Existing');
	$message->addTos([
		'new1@test.com' => 'New 1',
		'new2@test.com' => 'New 2',
	]);

	Assert::equal([
		'existing@test.com' => 'Existing',
		'new1@test.com' => 'New 1',
		'new2@test.com' => 'New 2',
	], $message->getHeader('To'));
});

// Test addTos with single recipient
Toolkit::test(function (): void {
	$message = new Message();
	$message->addTos([
		'single@test.com' => 'Single',
	]);

	Assert::equal(['single@test.com' => 'Single'], $message->getHeader('To'));
});

// Test Message extends Nette Message
Toolkit::test(function (): void {
	$message = new Message();

	Assert::type(\Nette\Mail\Message::class, $message);
});

// Test Message can use all parent methods
Toolkit::test(function (): void {
	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com', 'From Name');
	$message->addTo('to@test.com', 'To Name');
	$message->setBody('Test body');
	$message->setHtmlBody('<html><body>HTML body</body></html>');

	Assert::equal('Test Subject', $message->getSubject());
	Assert::equal(['from@test.com' => 'From Name'], $message->getHeader('From'));
	Assert::equal(['to@test.com' => 'To Name'], $message->getHeader('To'));
});
