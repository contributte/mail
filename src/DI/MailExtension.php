<?php declare(strict_types = 1);

namespace Contributte\Mail\DI;

use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Message\IMessageFactory;
use Contributte\Mail\Tracy\MailPanel;
use Nette\DI\CompilerExtension;
use Nette\Mail\Mailer;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class MailExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'trace' => Expect::bool(false),
		]);
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('messageFactory'))
			->setImplement(IMessageFactory::class);
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		if (!$this->config->trace) {
			return;
		}

		$builder = $this->getContainerBuilder();

		// Disable autowiring for default mailer
		$originalMailer = $builder->getDefinitionByType(Mailer::class);
		$originalMailer->setAutowired(false);

		// Wrap original mailer by TraceableMailer
		$traceableMailer = $builder->addDefinition($this->prefix('mailer'))
			->setType(TraceableMailer::class)
			->setArguments([$originalMailer]);

		// Mail panel for tracy
		$builder->addDefinition($this->prefix('panel'))
			->setFactory(MailPanel::class)
			->setAutowired(false)
			->addSetup('setTraceableMailer', [$traceableMailer]);
	}

	/**
	 * Show mail panel in tracy
	 */
	public function afterCompile(ClassType $class): void
	{
		if (!$this->config->trace) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$initialize->addBody(
			'$this->getService(?)->addPanel($this->getService(?));',
			['tracy.bar', $this->prefix('panel')]
		);
	}

}
