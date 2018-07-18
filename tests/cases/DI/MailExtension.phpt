<?php declare(strict_types = 1);

namespace Tests\DI;

/**
 * Test: DI\MailExtension
 */

use Contributte\Mail\DI\MailExtension;
use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Contributte\Mail\Mailer\FileMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Nette\Bridges\MailDI\MailExtension as NetteMailExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\Mail\IMailer;
use Nette\Mail\SendmailMailer;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('mail', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
		mail:
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class();

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('mail', new NetteMailExtension());
		$compiler->addExtension('post', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
		post:
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class();

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
	Assert::type(SendmailMailer::class, $container->getService('nette.mailer'));
	Assert::type(SendmailMailer::class, $container->getService('mail.mailer'));
	Assert::type(FileMailer::class, $container->getService('post.mailer'));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('mail', new NetteMailExtension());
		$compiler->addExtension('post', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
		post:
			mode: override
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		', 'neon'));
	}, 4);

	/** @var Container $container */
	$container = new $class();

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
	Assert::type(FileMailer::class, $container->getService('nette.mailer'));
	Assert::type(FileMailer::class, $container->getService('mail.mailer'));
	Assert::type(FileMailer::class, $container->getService('post.mailer'));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('mail', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
		mail:
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
			debug: true
		', 'neon'));
	}, 5);

	/** @var Container $container */
	$container = new $class();

	Assert::type(TraceableMailer::class, $container->getByType(IMailer::class));
});

test(function (): void {
	Assert::throws(function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('post', new MailExtension());
			$compiler->loadConfig(FileMock::create('
		post:
			mode: foobar
		', 'neon'));
		}, 6);
	}, InvalidArgumentException::class, 'Invalid mode "foobar", allowed are [ standalone | override ]');
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('post', new MailExtension());
		$compiler->loadConfig(FileMock::create('
		services:
			post.mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		post:
			mailer: Contributte\Mail\Mailer\SmtpMailer
		', 'neon'));
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
	}, 7);

	/** @var Container $container */
	$container = new $class();

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
});
