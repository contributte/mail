<?php declare(strict_types = 1);

namespace Contributte\Mail\DI;

use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Message\IMessageFactory;
use Contributte\Mail\Tracy\MailPanel;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class MailExtension extends CompilerExtension
{

	// Modes
	public const MODE_STANDALONE = 'standalone';
	public const MODE_OVERRIDE = 'override';

	public const MODES = [
		self::MODE_STANDALONE,
		self::MODE_OVERRIDE,
	];

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'mode' => Expect::anyOf(...self::MODES)->default(self::MODE_STANDALONE),
			'mailer' => Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))->required(),
			'debug' => Expect::bool(false),
		]);
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;
		$definitionsHelper = new ExtensionDefinitionsHelper($this->compiler);

		$builder->addFactoryDefinition($this->prefix('messageFactory'))
			->setImplement(IMessageFactory::class);

		// Wrap original mailer by TraceableMailer
		if ($config->debug) {
			$originalMailer = $definitionsHelper->getDefinitionFromConfig($config->mailer, $this->prefix('mailer.original'));

			if ($originalMailer instanceof Definition) {
				$originalMailer->setAutowired(false);
			}

			$traceableMailer = $builder->addDefinition($this->prefix('mailer'))
				->setType(TraceableMailer::class)
				->setArguments([$originalMailer]);

			// Mail panel for tracy
			$builder->addDefinition($this->prefix('panel'))
				->setFactory(MailPanel::class)
				->addSetup('setTraceableMailer', [$traceableMailer]);
		} else {
			// Load mailer
			$definitionsHelper->getDefinitionFromConfig($config->mailer, $this->prefix('mailer'));
		}
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		// Handle nette/mail configuration
		if ($this->name === 'mail') {
			return;
		}

		if ($config->mode === self::MODE_STANDALONE) {
			// Disable autowiring of nette.mailer
			if ($builder->hasDefinition('mail.mailer')) {
				$builder->getDefinition('mail.mailer')
					->setAutowired(false);
			}
		} elseif ($config->mode === self::MODE_OVERRIDE) {
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
		$config = $this->config;

		if ($config->debug === true) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				['tracy.bar', $this->prefix('panel')]
			);
		}
	}

}
