<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Throwable;

class CompositeMailer implements Mailer
{

	/** @var Mailer[] */
	private array $mailers = [];

	private bool $silent;

	public function __construct(bool $silent = false)
	{
		$this->silent = $silent;
	}

	public function add(Mailer $mailer): void
	{
		$this->mailers[] = $mailer;
	}

	/**
	 * @throw Exception
	 */
	public function send(Message $mail): void
	{
		foreach ($this->mailers as $mailer) {
			try {
				$mailer->send(clone $mail);
			} catch (Throwable $e) {
				if (!$this->silent) {
					throw $e;
				}
			}
		}
	}

}
