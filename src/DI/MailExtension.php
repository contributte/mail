<?php

namespace Contributte\Mail\DI;

use Contributte\Mail\Message\MessageFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\InvalidArgumentException;

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

		if (!in_array($config['mode'], self::MODES)) {
			throw new InvalidArgumentException(sprintf('Invalid mode "%s", allowed are [ %s ]', $config['mode'], implode(' | ', self::MODES)));
		}

		$builder->addDefinition($this->prefix('messageFactory'))
			->setClass(MessageFactory::class);

		if ($config['mailer']) {
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

		if (!$config['mailer']) return;

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

}
