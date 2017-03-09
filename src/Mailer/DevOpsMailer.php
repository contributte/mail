<?php

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class DevOpsMailer implements IMailer
{

	/** @var IMailer */
	private $mailer;

	/** @var string */
	private $mail;

	/**
	 * @param IMailer $mailer
	 * @param string $mail
	 */
	public function __construct(IMailer $mailer, $mail)
	{
		$this->mailer = $mailer;
		$this->mail = $mail;
	}

	/**
	 * Sends email
	 *
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		// Append this commands cause bounce email
		$this->commandArgs = sprintf('-f%s', $this->mail);

		// Set original To, Cc, Bcc
		$counter = 0;
		foreach ((array) $mail->getHeader('To') as $email => $name) {
			$mail->setHeader('X-Original-To-' . $counter++, sprintf('<%s> %s', $email, $name));
		}

		$counter = 0;
		foreach ((array) $mail->getHeader('Cc') as $email => $name) {
			$mail->setHeader('X-Original-Cc-' . $counter++, sprintf('<%s> %s', $email, $name));
		}

		$counter = 0;
		foreach ((array) $mail->getHeader('Bcc') as $email => $name) {
			$mail->setHeader('X-Original-Bcc-' . $counter++, sprintf('<%s> %s', $email, $name));
		}

		// Override for DevOps
		$mail->setHeader('To', [$this->mail => 'DevOps']);
		$mail->setHeader('Cc', NULL);
		$mail->setHeader('Bcc', NULL);

		// Delegate to original mailer
		$this->mailer->send($mail);
	}

}
