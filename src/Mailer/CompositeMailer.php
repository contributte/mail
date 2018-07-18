<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Exception;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Throwable;

final class CompositeMailer implements IMailer
{

	/** @var IMailer[] */
	private $mailers = [];

	/** @var bool */
	private $silent;

	public function __construct(bool $silent)
	{
		$this->silent = $silent;
	}

	public function add(IMailer $mailer): void
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
