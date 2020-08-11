<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

/**
 * Test: DI\MailExtension
 */

use Contributte\Mail\DI\MailExtension;
use Contributte\Mail\Mailer\FileMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Nette\Bridges\MailDI\MailExtension as NetteMailExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Nette\Mail\Mailer;
use Nette\Mail\SendmailMailer;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

// Missing mailer
test(function (): void {
	Assert::exception(function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('post', new MailExtension());
			$compiler->loadConfig(FileMock::create('
			post:
				trace: true
			', 'neon'));
		}, 1);

		new $class();
	}, MissingServiceException::class);
});

// Default
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('mail', new NetteMailExtension());
		$compiler->addExtension('post', new MailExtension());
		$compiler->loadConfig(FileMock::create('
			post:
				trace: true
			', 'neon'));
	}, 2);

	/** @var Container $container */
	$container = new $class();

	Assert::type(TraceableMailer::class, $container->getByType(Mailer::class));
	Assert::type(SendmailMailer::class, $container->getService('nette.mailer'));
	Assert::type(SendmailMailer::class, $container->getService('mail.mailer'));
	Assert::type(TraceableMailer::class, $container->getService('post.mailer'));
});

// Custom mailer
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('post', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
			services:
				mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
			post:
				trace: true
			', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class();

	Assert::type(TraceableMailer::class, $container->getByType(Mailer::class));
	Assert::type(FileMailer::class, $container->getService('mailer'));
});
