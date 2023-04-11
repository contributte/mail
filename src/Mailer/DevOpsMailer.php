<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;

final class DevOpsMailer implements Mailer
{

	private Mailer $mailer;

	private string $mail;

	public function __construct(Mailer $mailer, string $mail)
	{
		$this->mailer = $mailer;
		$this->mail = $mail;
	}

	/**
	 * Sends email
	 */
	public function send(Message $mail): void
	{
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
		$mail->setHeader('Cc', null);
		$mail->setHeader('Bcc', null);

		// Delegate to original mailer
		$this->mailer->send($mail);
	}

}
