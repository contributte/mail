<?php

namespace Contributte\Mail\DI;

use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Message\MessageFactory;
use Contributte\Mail\Tracy\MailPanel;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\InvalidArgumentException;
use Nette\PhpGenerator\ClassType;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class MailExtension extends CompilerExtension
{

	// Modes
	const MODE_STANDALONE = 'standalone';
	const MODE_OVERRIDE = 'override';

	const MODES = [
		self::MODE_STANDALONE,
		self::MODE_OVERRIDE,
	];

	/** @var array */
	private $defaults = [
		'mode' => self::MODE_STANDALONE,
		'mailer' => NULL,
		'debug' => FALSE,
	];

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if (!in_array($config['mode'], self::MODES, TRUE)) {
			throw new InvalidArgumentException(sprintf('Invalid mode "%s", allowed are [ %s ]', $config['mode'], implode(' | ', self::MODES)));
		}

		$builder->addDefinition($this->prefix('messageFactory'))
			->setClass(MessageFactory::class);

		if ($config['debug']) {
			// Wrap original mailer by TraceableMailer
			$mailer = $builder->addDefinition($this->prefix('mailer.original'))
				->setAutowired(FALSE);// So that contributte/mailing doesn't see multiple IMailer
			Compiler::loadDefinition($mailer, $config['mailer']);// So that we can use mailer: {class: Sendmail, setup: setBounceEmail:...}

			$traceableMailer = $builder->addDefinition($this->prefix('mailer'))
				->setFactory(TraceableMailer::class, [$mailer]);

			// Mail panel for tracy
			$builder->addDefinition($this->prefix('mail.panel'))
				->setFactory(MailPanel::class)
				->addSetup('setTraceableMailer', [$traceableMailer]);
		} else {
			// Load mailer
			$mailer = $builder->addDefinition($this->prefix('mailer'));
			Compiler::loadDefinition($mailer, $config['mailer']);
		}
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// Handle nette/mail configuration
		if ($this->name !== 'mail') {

			if ($config['mode'] === self::MODE_STANDALONE) {
				// Disable autowiring of nette.mailer
				if ($builder->hasDefinition('mail.mailer')) {
					$builder->getDefinition('mail.mailer')
						->setAutowired(FALSE);
				}
			} else if ($config['mode'] === self::MODE_OVERRIDE) {
				// Remove nette.mailer and replace with our mailer
				$builder->removeDefinition('mail.mailer');
				$builder->addAlias('mail.mailer', $this->prefix('mailer'));
			}
		}
	}

	/**
	 * Show mail panel in tracy
	 *
	 * @param ClassType $class
	 * @return void
	 */
	public function afterCompile(ClassType $class)
	{
		$config = $this->validateConfig($this->defaults);
		if ($config['debug']) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				['tracy.bar', $this->prefix('mail.panel')]
			);
		}
	}

}
