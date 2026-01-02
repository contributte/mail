<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test that DevNullMailer implements Mailer interface and accepts Message
Toolkit::test(function (): void {
	$mailer = new DevNullMailer();

	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');
	$message->setBody('Test body');

	// Should not throw any exception
	$mailer->send($message);

	// Message should remain unchanged after sending
	Assert::equal('Test Subject', $message->getSubject());
	Assert::equal(['to@test.com' => null], $message->getHeader('To'));
});

// Test that multiple messages can be sent without issues
Toolkit::test(function (): void {
	$mailer = new DevNullMailer();

	for ($i = 0; $i < 10; $i++) {
		$message = new Message();
		$message->setSubject('Test ' . $i);
		$message->setFrom('from@test.com');
		$message->addTo('to@test.com');

		$mailer->send($message);
	}

	// No assertion needed - just ensuring no exceptions are thrown
	Assert::true(true);
});
