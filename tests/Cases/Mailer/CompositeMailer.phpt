<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\CompositeMailer;
use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Tester\Toolkit;
use Exception;
use Mockery;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;
use Tests\Fixtures\ModifyMailer;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::tearDown(function (): void {
	Mockery::close();
});

// Test that CompositeMailer clones message so original is not modified
Toolkit::test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ModifyMailer());

	$message = new Message();
	$message->setSubject('foobar');

	$cm->send($message);

	Assert::equal('foobar', $message->getSubject());
});

// Test that CompositeMailer sends to all added mailers
Toolkit::test(function (): void {
	$mailer1 = Mockery::mock(Mailer::class);
	$mailer1->shouldReceive('send')
		->once()
		->with(Mockery::type(Message::class));

	$mailer2 = Mockery::mock(Mailer::class);
	$mailer2->shouldReceive('send')
		->once()
		->with(Mockery::type(Message::class));

	$cm = new CompositeMailer();
	$cm->add($mailer1);
	$cm->add($mailer2);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	$cm->send($message);

	Mockery::getContainer()->mockery_verify();
});

// Test that CompositeMailer with silent=false throws exception
Toolkit::test(function (): void {
	$failingMailer = Mockery::mock(Mailer::class);
	$failingMailer->shouldReceive('send')
		->once()
		->andThrow(new Exception('Mailer failed'));

	$cm = new CompositeMailer(false);
	$cm->add($failingMailer);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	Assert::exception(function () use ($cm, $message): void {
		$cm->send($message);
	}, Exception::class, 'Mailer failed');

	Mockery::getContainer()->mockery_verify();
});

// Test that CompositeMailer with silent=true swallows exception
Toolkit::test(function (): void {
	$failingMailer = Mockery::mock(Mailer::class);
	$failingMailer->shouldReceive('send')
		->once()
		->andThrow(new Exception('Mailer failed'));

	$cm = new CompositeMailer(true);
	$cm->add($failingMailer);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	// Should not throw
	$cm->send($message);

	Mockery::getContainer()->mockery_verify();
});

// Test that CompositeMailer with silent=true continues to next mailer after failure
Toolkit::test(function (): void {
	$failingMailer = Mockery::mock(Mailer::class);
	$failingMailer->shouldReceive('send')
		->once()
		->andThrow(new Exception('Mailer failed'));

	$successMailer = Mockery::mock(Mailer::class);
	$successMailer->shouldReceive('send')
		->once()
		->with(Mockery::type(Message::class));

	$cm = new CompositeMailer(true);
	$cm->add($failingMailer);
	$cm->add($successMailer);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	// Should not throw and should call successMailer
	$cm->send($message);

	Mockery::getContainer()->mockery_verify();
});

// Test CompositeMailer with no mailers
Toolkit::test(function (): void {
	$cm = new CompositeMailer();

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	// Should not throw
	$cm->send($message);

	Assert::true(true);
});

// Test default silent mode is false
Toolkit::test(function (): void {
	$failingMailer = Mockery::mock(Mailer::class);
	$failingMailer->shouldReceive('send')
		->once()
		->andThrow(new Exception('Default mode should throw'));

	$cm = new CompositeMailer(); // default silent = false
	$cm->add($failingMailer);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	Assert::exception(function () use ($cm, $message): void {
		$cm->send($message);
	}, Exception::class, 'Default mode should throw');

	Mockery::getContainer()->mockery_verify();
});
