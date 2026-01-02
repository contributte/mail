<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Contributte\Mail\Mailer\SendmailMailer;
use Contributte\Tester\Toolkit;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer as NetteSendmailMailer;
use Tester\Assert;
use Tests\Fixtures\TestSendmailMailer;

require_once __DIR__ . '/../../bootstrap.php';

// Test that SendmailMailer extends Nette SendmailMailer
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();
	Assert::type(NetteSendmailMailer::class, $mailer);
	Assert::type(Mailer::class, $mailer);
});

// Test valid bounce mail
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();
	$mailer->setBounceMail('bounce@example.com');

	// No exception means success
	Assert::true(true);
});

// Test invalid bounce mail throws exception
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('invalid-email');
	}, InvalidArgumentException::class, 'Bounce mail invalid-email has wrong format');
});

// Test invalid bounce mail - missing domain
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('invalid@');
	}, InvalidArgumentException::class);
});

// Test invalid bounce mail - missing local part
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('@example.com');
	}, InvalidArgumentException::class);
});

// Test invalid bounce mail - empty string
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('');
	}, InvalidArgumentException::class);
});

// Test onSend event is triggered
Toolkit::test(function (): void {
	$mailer = new TestSendmailMailer();

	$eventTriggered = false;
	$capturedMailer = null;
	$capturedMail = null;

	$mailer->onSend[] = function ($m, $mail) use (&$eventTriggered, &$capturedMailer, &$capturedMail): void {
		$eventTriggered = true;
		$capturedMailer = $m;
		$capturedMail = $mail;
	};

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	$mailer->send($message);

	Assert::true($eventTriggered);
	Assert::same($mailer, $capturedMailer);
	Assert::same($message, $capturedMail);
});

// Test multiple onSend callbacks
Toolkit::test(function (): void {
	$mailer = new TestSendmailMailer();

	$callCount = 0;

	$mailer->onSend[] = function () use (&$callCount): void {
		$callCount++;
	};
	$mailer->onSend[] = function () use (&$callCount): void {
		$callCount++;
	};
	$mailer->onSend[] = function () use (&$callCount): void {
		$callCount++;
	};

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	$mailer->send($message);

	Assert::same(3, $callCount);
});

// Test onSend events array is empty by default
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();
	Assert::same([], $mailer->onSend);
});
