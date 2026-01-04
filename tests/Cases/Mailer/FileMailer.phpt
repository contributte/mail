<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\FileMailer;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Utils\FileSystem;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test that FileMailer implements Mailer interface
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer1';
	$mailer = new FileMailer($tempDir);
	Assert::type(Mailer::class, $mailer);
	FileSystem::delete($tempDir);
});

// Test that directory is created on construction
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer2';
	Assert::false(is_dir($tempDir));

	new FileMailer($tempDir);
	Assert::true(is_dir($tempDir));

	FileSystem::delete($tempDir);
});

// Test that email is saved as .eml file
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer3';
	$mailer = new FileMailer($tempDir);

	$message = new Message();
	$message->setFrom('from@example.com', 'Sender Name');
	$message->addTo('to@example.com', 'Recipient Name');
	$message->setSubject('Test Subject');
	$message->setBody('Test body content');

	$mailer->send($message);

	// Check that .eml file was created
	$files = glob($tempDir . '/*.eml');
	Assert::count(1, $files);

	// Check file content contains message data
	$content = file_get_contents($files[0]);
	Assert::contains('Test Subject', $content);
	Assert::contains('from@example.com', $content);
	Assert::contains('to@example.com', $content);
	Assert::contains('Test body content', $content);

	FileSystem::delete($tempDir);
});

// Test multiple emails create multiple files
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer4';
	$mailer = new FileMailer($tempDir);

	for ($i = 0; $i < 3; $i++) {
		$message = new Message();
		$message->setFrom('from@example.com');
		$message->addTo('to@example.com');
		$message->setSubject('Test Subject ' . $i);
		$message->setBody('Body ' . $i);

		$mailer->send($message);
		usleep(1000); // Small delay to ensure unique filenames
	}

	$files = glob($tempDir . '/*.eml');
	Assert::count(3, $files);

	FileSystem::delete($tempDir);
});

// Test email with HTML body
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer5';
	$mailer = new FileMailer($tempDir);

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('HTML Test');
	$message->setHtmlBody('<html><body><h1>Hello World</h1></body></html>');

	$mailer->send($message);

	$files = glob($tempDir . '/*.eml');
	Assert::count(1, $files);

	$content = file_get_contents($files[0]);
	Assert::contains('HTML Test', $content);
	Assert::contains('Hello World', $content);

	FileSystem::delete($tempDir);
});
