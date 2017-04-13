<?php

namespace Tests\DI;

/**
 * Test: DI\MailExtension
 */

use Contributte\Mail\DI\MailExtension;
use Contributte\Mail\Mailer\FileMailer;
use Contributte\Mail\Message\MessageFactory;
use Nette\Bridges\MailDI\MailExtension as NetteMailExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\InvalidArgumentException;
use Nette\Mail\IMailer;
use Nette\Mail\SendmailMailer;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
	}, 3);

	/** @var Container $container */
	$container = new $class;

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
	Assert::type(SendmailMailer::class, $container->getService('nette.mailer'));
	Assert::type(SendmailMailer::class, $container->getService('mail.mailer'));
	Assert::type(FileMailer::class, $container->getService('post.mailer'));
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
			mode: override
			mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
		', 'neon'));
	}, 4);

	/** @var Container $container */
	$container = new $class;

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
	Assert::type(FileMailer::class, $container->getService('nette.mailer'));
	Assert::type(FileMailer::class, $container->getService('mail.mailer'));
	Assert::type(FileMailer::class, $container->getService('post.mailer'));
});

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('post', new MailExtension());
	}, 5);

	/** @var Container $container */
	$container = new $class;

	Assert::type(MessageFactory::class, $container->getByType(MessageFactory::class));
	Assert::false($container->hasService('post.mailer'));
});

test(function () {
	Assert::throws(function () {
		$loader = new ContainerLoader(TEMP_DIR, TRUE);
		$class = $loader->load(function (Compiler $compiler) {
			$compiler->addExtension('post', new MailExtension());
			$compiler->loadConfig(FileMock::create('
		post:
			mode: foobar
		', 'neon'));
		}, 6);
	}, InvalidArgumentException::class, 'Invalid mode "foobar", allowed are [ standalone | override ]');
});

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
	$container = new $class;

	Assert::type(FileMailer::class, $container->getByType(IMailer::class));
});
