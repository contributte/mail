<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Tester\Toolkit;
use Mockery;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::tearDown(function (): void {
	Mockery::close();
});

// Test that TraceableMailer starts with empty mails array
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	Assert::same([], $mailer->getMails());
	Assert::count(0, $mailer->getMails());
});

// Test that TraceableMailer records sent messages
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	$mailer->send($message);

	Assert::count(1, $mailer->getMails());
	Assert::same($message, $mailer->getMails()[0]);
	Assert::equal('Test Subject', $mailer->getMails()[0]->getSubject());
});

// Test that multiple messages are recorded
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	$messages = [];
	for ($i = 0; $i < 5; $i++) {
		$message = new Message();
		$message->setSubject('Test Subject ' . $i);
		$message->setFrom('from@test.com');
		$message->addTo('to@test.com');
		$messages[] = $message;

		$mailer->send($message);
	}

	Assert::count(5, $mailer->getMails());

	foreach ($messages as $index => $expected) {
		Assert::same($expected, $mailer->getMails()[$index]);
	}
});

// Test that TraceableMailer delegates to inner mailer
Toolkit::test(function (): void {
	$innerMailer = Mockery::mock(Mailer::class);
	$innerMailer->shouldReceive('send')
		->once()
		->with(Mockery::type(Message::class));

	$mailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	$mailer->send($message);

	Mockery::getContainer()->mockery_verify();
});
