<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\DevOpsMailer;
use Contributte\Tester\Toolkit;
use Mockery;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::tearDown(function (): void {
	Assert::notEqual(0, Mockery::getContainer()->mockery_getExpectationCount());
	Mockery::getContainer()->mockery_teardown();
});

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
});
