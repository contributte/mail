<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Mail\Mailer\DevOpsMailer;
use Contributte\Tester\Toolkit;
use Mockery;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::tearDown(function (): void {
	Mockery::close();
});

// Test basic header modification
Toolkit::test(function (): void {
	$mailer = Mockery::mock(Mailer::class);
	$mailer->shouldReceive('send')
		->once();

	$devopsMailer = new DevOpsMailer($mailer, 'catchall@contributte.org');

	$messageMock = Mockery::mock(Message::class)->makePartial();
	$messageMock->setSubject('Test');
	$messageMock->addTo('john@contributte.org');
	$messageMock->shouldReceive('setHeader')
		->with('Bcc', null)
		->once();
	$messageMock->shouldReceive('setHeader')
		->with('To', ['catchall@contributte.org' => 'DevOps'])
		->once();
	$messageMock->shouldReceive('setHeader')
		->with('Cc', null)
		->once();

	$devopsMailer->send($messageMock);

	Mockery::getContainer()->mockery_verify();
});

// Test that original headers are preserved as X-Original-* headers
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$devopsMailer = new DevOpsMailer($innerMailer, 'catchall@test.com');

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('original-to@test.com', 'Original To');
	$message->addCc('original-cc@test.com', 'Original Cc');
	$message->addBcc('original-bcc@test.com', 'Original Bcc');

	$devopsMailer->send($message);

	// Check X-Original headers
	Assert::same('<original-to@test.com> Original To', $message->getHeader('X-Original-To-0'));
	Assert::same('<original-cc@test.com> Original Cc', $message->getHeader('X-Original-Cc-0'));
	Assert::same('<original-bcc@test.com> Original Bcc', $message->getHeader('X-Original-Bcc-0'));

	// Check that To is replaced
	Assert::same(['catchall@test.com' => 'DevOps'], $message->getHeader('To'));

	// Check that Cc and Bcc are null
	Assert::null($message->getHeader('Cc'));
	Assert::null($message->getHeader('Bcc'));
});

// Test with multiple recipients
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$devopsMailer = new DevOpsMailer($innerMailer, 'catchall@test.com');

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to1@test.com', 'To 1');
	$message->addTo('to2@test.com', 'To 2');
	$message->addCc('cc1@test.com', 'Cc 1');
	$message->addCc('cc2@test.com', 'Cc 2');

	$devopsMailer->send($message);

	// Check multiple X-Original headers
	Assert::same('<to1@test.com> To 1', $message->getHeader('X-Original-To-0'));
	Assert::same('<to2@test.com> To 2', $message->getHeader('X-Original-To-1'));
	Assert::same('<cc1@test.com> Cc 1', $message->getHeader('X-Original-Cc-0'));
	Assert::same('<cc2@test.com> Cc 2', $message->getHeader('X-Original-Cc-1'));
});

// Test with no CC and BCC
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$devopsMailer = new DevOpsMailer($innerMailer, 'catchall@test.com');

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com', 'To');

	$devopsMailer->send($message);

	// Check that X-Original-To is set
	Assert::same('<to@test.com> To', $message->getHeader('X-Original-To-0'));

	// Check that To is replaced
	Assert::same(['catchall@test.com' => 'DevOps'], $message->getHeader('To'));
});

// Test delegation to inner mailer
Toolkit::test(function (): void {
	$innerMailer = Mockery::mock(Mailer::class);
	$innerMailer->shouldReceive('send')
		->once()
		->with(Mockery::type(Message::class));

	$devopsMailer = new DevOpsMailer($innerMailer, 'catchall@test.com');

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	$devopsMailer->send($message);

	Mockery::getContainer()->mockery_verify();
});
