<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Contributte\Mail\Mailer\SendmailMailer;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test setBounceMail with valid email
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	// Should not throw exception
	$mailer->setBounceMail('bounce@test.com');

	Assert::true(true);
});

// Test setBounceMail with invalid email throws exception
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('invalid-email');
	}, InvalidArgumentException::class, 'Bounce mail invalid-email has wrong format');
});

// Test setBounceMail with another invalid email
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::exception(function () use ($mailer): void {
		$mailer->setBounceMail('not-an-email@');
	}, InvalidArgumentException::class);
});

// Test onSend callback is executed
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	$callbackExecuted = false;
	$receivedMailer = null;
	$receivedMessage = null;

	$mailer->onSend[] = function ($m, $msg) use (&$callbackExecuted, &$receivedMailer, &$receivedMessage): void {
		$callbackExecuted = true;
		$receivedMailer = $m;
		$receivedMessage = $msg;
	};

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	// Note: This will fail to actually send because sendmail is not available
	// but the callback should still be executed before the send attempt
	try {
		$mailer->send($message);
	} catch (\Throwable $e) {
		// Expected to fail on send, but callback should have been executed
	}

	Assert::true($callbackExecuted);
	Assert::same($mailer, $receivedMailer);
	Assert::same($message, $receivedMessage);
});

// Test multiple onSend callbacks are executed
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	$callback1Executed = false;
	$callback2Executed = false;

	$mailer->onSend[] = function () use (&$callback1Executed): void {
		$callback1Executed = true;
	};

	$mailer->onSend[] = function () use (&$callback2Executed): void {
		$callback2Executed = true;
	};

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');

	try {
		$mailer->send($message);
	} catch (\Throwable $e) {
		// Expected to fail on send
	}

	Assert::true($callback1Executed);
	Assert::true($callback2Executed);
});

// Test onSend array is public and accessible
Toolkit::test(function (): void {
	$mailer = new SendmailMailer();

	Assert::same([], $mailer->onSend);
	Assert::type('array', $mailer->onSend);
});
