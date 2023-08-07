<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;

class TraceableMailer implements Mailer
{

	/** @var array<Message> */
	private array $mails = [];

	public function __construct(private Mailer $mailer)
	{
	}

	public function send(Message $mail): void
	{
		// Trace sent mails
		$this->mails[] = $mail;

		// Delegate to original mailer
		$this->mailer->send($mail);
	}

	/**
	 * @return array<Message>
	 */
	public function getMails(): array
	{
		return $this->mails;
	}

}
