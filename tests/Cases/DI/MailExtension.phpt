<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Mail\DI\MailExtension;
use Contributte\Mail\Mailer\FileMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Nette\Bridges\MailDI\MailExtension as NetteMailExtension;
use Nette\DI\Compiler;
use Nette\DI\MissingServiceException;
use Nette\Mail\Mailer;
use Nette\Mail\SendmailMailer;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Missing mailer
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('post', new MailExtension());
				$compiler->addConfig(Neonkit::load('
				post:
					trace: true
				'));
			})->build();
	}, MissingServiceException::class);
});

// Default
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('mail', new NetteMailExtension());
			$compiler->addExtension('post', new MailExtension());
			$compiler->addConfig(Neonkit::load('
				post:
					trace: true
				'));
		})->build();

	Assert::type(TraceableMailer::class, $container->getByType(Mailer::class));
	Assert::type(SendmailMailer::class, $container->getService('nette.mailer'));
	Assert::type(SendmailMailer::class, $container->getService('mail.mailer'));
	Assert::type(TraceableMailer::class, $container->getService('post.mailer'));
});

// Custom mailer
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('post', new MailExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
				],
			]);
			$compiler->addConfig(Neonkit::load('
			services:
				mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
			post:
				trace: true
			'));
		})->build();
	Assert::type(TraceableMailer::class, $container->getByType(Mailer::class));
	Assert::type(FileMailer::class, $container->getService('mailer'));
});
