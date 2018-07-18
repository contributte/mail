<?php

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class TraceableMailer implements IMailer
{

	/** @var IMailer */
	private $mailer;

	/** @var Message[] */
	private $mails = [];

	/**
	 * @param IMailer $mailer
	 */
	public function __construct(Imailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * Sends email
	 *
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		// Trace sent mails
		$this->mails[] = $mail;

		// Delegate to original mailer
		$this->mailer->send($mail);
	}

	/**
	 * @return Message[]
	 */
	public function getMails()
	{
		return $this->mails;
	}

}
