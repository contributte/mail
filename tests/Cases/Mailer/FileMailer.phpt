<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\FileMailer;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Nette\Utils\FileSystem;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test that FileMailer creates directory on instantiation
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer_' . uniqid();

	Assert::false(is_dir($tempDir));

	$mailer = new FileMailer($tempDir);

	Assert::true(is_dir($tempDir));

	FileSystem::delete($tempDir);
});

// Test that FileMailer saves email as .eml file
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer_' . uniqid();

	$mailer = new FileMailer($tempDir);

	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');
	$message->setBody('Test body');

	$mailer->send($message);

	// Check that exactly one .eml file was created
	$files = glob($tempDir . '/*.eml');
	Assert::count(1, $files);

	// Check that file contains the message
	$content = file_get_contents($files[0]);
	Assert::contains('Test Subject', $content);
	Assert::contains('from@test.com', $content);
	Assert::contains('to@test.com', $content);
	Assert::contains('Test body', $content);

	FileSystem::delete($tempDir);
});

// Test that multiple emails create multiple files
Toolkit::test(function (): void {
	$tempDir = Environment::getTestDir() . '/fileMailer_' . uniqid();

	$mailer = new FileMailer($tempDir);

	for ($i = 0; $i < 3; $i++) {
		$message = new Message();
		$message->setSubject('Test Subject ' . $i);
		$message->setFrom('from@test.com');
		$message->addTo('to@test.com');
		$message->setBody('Test body ' . $i);

		$mailer->send($message);

		// Small delay to ensure different filenames (based on microtime)
		usleep(1000);
	}

	// Check that three .eml files were created
	$files = glob($tempDir . '/*.eml');
	Assert::count(3, $files);

	FileSystem::delete($tempDir);
});
