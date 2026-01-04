<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\CompositeMailer;
use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Tester\Toolkit;
use Exception;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;
use Tests\Fixtures\ModifyMailer;
use Tests\Fixtures\ThrowingMailer;

require_once __DIR__ . '/../../bootstrap.php';

// Test that CompositeMailer implements Mailer interface
Toolkit::test(function (): void {
	$cm = new CompositeMailer();
	Assert::type(Mailer::class, $cm);
});

// Original test - message is cloned so original is not modified
Toolkit::test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ModifyMailer());

	$message = new Message();
	$message->setSubject('foobar');

	$cm->send($message);

	Assert::equal('foobar', $message->getSubject());
});

// Test without any mailers
Toolkit::test(function (): void {
	$cm = new CompositeMailer();

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	// Should not throw
	$cm->send($message);
	Assert::true(true);
});

// Test with multiple mailers
Toolkit::test(function (): void {
	$counter = new class implements Mailer {

		public int $count = 0;

		public function send(Message $mail): void
		{
			$this->count++;
		}

	};

	$cm = new CompositeMailer();
	$cm->add($counter);
	$cm->add($counter);
	$cm->add($counter);

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	$cm->send($message);

	Assert::same(3, $counter->count);
});

// Test silent mode swallows exceptions
Toolkit::test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ThrowingMailer());
	$cm->add(new DevNullMailer());

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	// Should not throw in silent mode
	$cm->send($message);
	Assert::true(true);
});

// Test non-silent mode throws exception
Toolkit::test(function (): void {
	$cm = new CompositeMailer(false);
	$cm->add(new ThrowingMailer());

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	Assert::exception(function () use ($cm, $message): void {
		$cm->send($message);
	}, Exception::class, 'Test exception');
});

// Test default is non-silent mode
Toolkit::test(function (): void {
	$cm = new CompositeMailer();
	$cm->add(new ThrowingMailer());

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	Assert::exception(function () use ($cm, $message): void {
		$cm->send($message);
	}, Exception::class);
});

// Test that each mailer receives a clone of the message
Toolkit::test(function (): void {
	$messages = [];
	$collector = new class ($messages) implements Mailer {

		/** @var array<Message> */
		private array $messages;

		public function __construct(array &$messages)
		{
			$this->messages = &$messages;
		}

		public function send(Message $mail): void
		{
			$this->messages[] = $mail;
		}

	};

	$cm = new CompositeMailer();
	$cm->add($collector);
	$cm->add($collector);

	$original = new Message();
	$original->setFrom('from@example.com');
	$original->addTo('to@example.com');
	$original->setSubject('Original');

	$cm->send($original);

	Assert::count(2, $messages);
	// Each mailer receives a clone, not the original
	Assert::notSame($original, $messages[0]);
	Assert::notSame($original, $messages[1]);
	Assert::notSame($messages[0], $messages[1]);
});

// Test silent mode continues to next mailer after exception
Toolkit::test(function (): void {
	$called = false;
	$afterException = new class ($called) implements Mailer {

		private bool $called;

		public function __construct(bool &$called)
		{
			$this->called = &$called;
		}

		public function send(Message $mail): void
		{
			$this->called = true;
		}

	};

	$cm = new CompositeMailer(true);
	$cm->add(new ThrowingMailer());
	$cm->add($afterException);

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');

	$cm->send($message);

	Assert::true($called);
});
