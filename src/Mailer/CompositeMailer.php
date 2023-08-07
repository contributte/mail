<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Throwable;

class CompositeMailer implements Mailer
{

	/** @var array<Mailer> */
	private array $mailers = [];

	public function __construct(private bool $silent = false)
	{
	}

	public function add(Mailer $mailer): void
	{
		$this->mailers[] = $mailer;
	}

	/**
	 * @throws Throwable
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
