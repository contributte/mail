<?php

/**
 * Test: DI\MailExtension
 */

use Contributte\Mail\DI\MailExtension;
use Contributte\Mail\Mailer\FileMailer;
use Nette\Bridges\MailDI\MailExtension as NetteMailExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Nette\Mail\IMailer;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
	}, 1);

	/** @var Container $container */
	$container = new $class;

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
});

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
	}, 2);

	/** @var Container $container */
	$container = new $class;

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
});

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('mail', new NetteMailExtension());
		$compiler->addExtension('post', new MailExtension());
		$compiler->addConfig([
			'parameters' => [
				'tempDir' => TEMP_DIR,
			],
		]);
		$compiler->loadConfig(FileMock::create('
		post:
			override: false
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class;

	Assert::exception(function () use ($container) {
		$container->getByType(IMailer::class);
	}, MissingServiceException::class, 'Multiple services of type Nette\Mail\IMailer found: mail.mailer, post.mailer.');
});
