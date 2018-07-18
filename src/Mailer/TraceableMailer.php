<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class TraceableMailer implements IMailer
{

	/** @var IMailer */
	private $mailer;

	/** @var Message[] */
	private $mails = [];

	public function __construct(IMailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * Sends email
	 */
	public function send(Message $mail): void
	{
		// Trace sent mails
		$this->mails[] = $mail;

		// Delegate to original mailer
		$this->mailer->send($mail);
	}

	/**
	 * @return Message[]
	 */
	public function getMails(): array
	{
		return $this->mails;
	}

}
