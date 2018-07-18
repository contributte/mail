<?php declare(strict_types = 1);

namespace Contributte\Mail\DI;

use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Contributte\Mail\Exception\Logic\InvalidStateException;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Message\IMessageFactory;
use Contributte\Mail\Tracy\MailPanel;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

class MailExtension extends CompilerExtension
{

	// Modes
	public const MODE_STANDALONE = 'standalone';
	public const MODE_OVERRIDE = 'override';

	public const MODES = [
		self::MODE_STANDALONE,
		self::MODE_OVERRIDE,
	];

	/** @var mixed[] */
	private $defaults = [
		'mode' => self::MODE_STANDALONE,
		'mailer' => null,
		'debug' => false,
	];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if (!in_array($config['mode'], self::MODES, true)) {
			throw new InvalidArgumentException(sprintf('Invalid mode "%s", allowed are [ %s ]', $config['mode'], implode(' | ', self::MODES)));
		}

		if ($config['mailer'] === null) {
			throw new InvalidStateException(sprintf('"%s" must be configured.', $this->prefix('mailer')));
		}

		$builder->addDefinition($this->prefix('messageFactory'))
			->setImplement(IMessageFactory::class);

		// Wrap original mailer by TraceableMailer
		if ($config['debug'] === true) {
			$mailer = $builder->addDefinition($this->prefix('mailer.original'))
				->setAutowired(false);
			Compiler::loadDefinition($mailer, $config['mailer']);

			$traceableMailer = $builder->addDefinition($this->prefix('mailer'))
				->setType(TraceableMailer::class)
				->setArguments([$mailer]);

			// Mail panel for tracy
			$builder->addDefinition($this->prefix('panel'))
				->setFactory(MailPanel::class)
				->addSetup('setTraceableMailer', [$traceableMailer]);
		} else {
			// Load mailer
			$mailer = $builder->addDefinition($this->prefix('mailer'))
				->setType($config['mailer']);
			Compiler::loadDefinition($mailer, $config['mailer']);
		}
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// Handle nette/mail configuration
		if ($this->name === 'mail') {
			return;
		}

		if ($config['mode'] === self::MODE_STANDALONE) {
			// Disable autowiring of nette.mailer
			if ($builder->hasDefinition('mail.mailer')) {
				$builder->getDefinition('mail.mailer')
					->setAutowired(false);
			}
		} elseif ($config['mode'] === self::MODE_OVERRIDE) {
			// Remove nette.mailer and replace with our mailer
			$builder->removeDefinition('mail.mailer');
			$builder->addAlias('mail.mailer', $this->prefix('mailer'));
		}
	}

	/**
	 * Show mail panel in tracy
	 */
	public function afterCompile(ClassType $class): void
	{
		$config = $this->validateConfig($this->defaults);
		if ($config['debug'] === true) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				['tracy.bar', $this->prefix('panel')]
			);
		}
	}

}
