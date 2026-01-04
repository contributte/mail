<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Tester\Toolkit;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test that DevNullMailer implements Mailer interface
Toolkit::test(function (): void {
	$mailer = new DevNullMailer();
	Assert::type(Mailer::class, $mailer);
});

// Test that send method does nothing (no exception)
Toolkit::test(function (): void {
	$mailer = new DevNullMailer();

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test Subject');
	$message->setBody('Test body');

	// Should not throw any exception
	$mailer->send($message);
	Assert::true(true);
});

// Test multiple sends
Toolkit::test(function (): void {
	$mailer = new DevNullMailer();

	for ($i = 0; $i < 10; $i++) {
		$message = new Message();
		$message->setFrom('from@example.com');
		$message->addTo('to@example.com');
		$message->setSubject('Test Subject ' . $i);

		$mailer->send($message);
	}

	Assert::true(true);
});
