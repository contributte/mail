<?php

namespace Contributte\Mail\DI;

use Contributte\Mail\Message\MessageFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class MailExtension extends CompilerExtension
{

	/** @var array */
	private $defaults = [
		'override' => TRUE,
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

		if ($config['mailer']) {
			$mailer = $builder->addDefinition($this->prefix('mailer'));
			Compiler::loadDefinition($mailer, $config['mailer']);

			if ($config['override'] === TRUE) {
				if ($this->name !== 'mail') {
					$builder->removeDefinition('mail.mailer');
					$builder->addAlias('mail.mailer', $this->prefix('mailer'));
				}

				$builder->removeAlias('nette.mailer');
				$builder->addAlias('nette.mailer', $this->prefix('mailer'));
			}
		}

		$builder->addDefinition($this->prefix('messageFactory'))
			->setClass(MessageFactory::class);
	}

}
