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

// Test that TraceableMailer implements Mailer interface
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);
	Assert::type(Mailer::class, $mailer);
});

// Test that getMails returns empty array initially
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	Assert::same([], $mailer->getMails());
});

// Test that sent mail is traced
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test Subject');

	$mailer->send($message);

	$mails = $mailer->getMails();
	Assert::count(1, $mails);
	Assert::same($message, $mails[0]);
	Assert::equal('Test Subject', $mails[0]->getSubject());
});

// Test that multiple sent mails are traced
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	$messages = [];
	for ($i = 0; $i < 5; $i++) {
		$message = new Message();
		$message->setFrom('from@example.com');
		$message->addTo('to@example.com');
		$message->setSubject('Test Subject ' . $i);
		$messages[] = $message;

		$mailer->send($message);
	}

	$mails = $mailer->getMails();
	Assert::count(5, $mails);

	for ($i = 0; $i < 5; $i++) {
		Assert::same($messages[$i], $mails[$i]);
		Assert::equal('Test Subject ' . $i, $mails[$i]->getSubject());
	}
});

// Test that inner mailer send is called
Toolkit::test(function (): void {
	$innerMailer = Mockery::mock(Mailer::class);
	$innerMailer->shouldReceive('send')
		->once();

	$mailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test Subject');

	$mailer->send($message);

	Assert::notEqual(0, Mockery::getContainer()->mockery_getExpectationCount());
});

// Test that mails order is preserved
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$mailer = new TraceableMailer($innerMailer);

	$subjects = ['First', 'Second', 'Third'];
	foreach ($subjects as $subject) {
		$message = new Message();
		$message->setFrom('from@example.com');
		$message->addTo('to@example.com');
		$message->setSubject($subject);
		$mailer->send($message);
	}

	$mails = $mailer->getMails();
	Assert::equal('First', $mails[0]->getSubject());
	Assert::equal('Second', $mails[1]->getSubject());
	Assert::equal('Third', $mails[2]->getSubject());
});
