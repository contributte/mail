<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;

class DevOpsMailer implements Mailer
{

	public function __construct(private Mailer $mailer, private string $mail)
	{
	}

	public function send(Message $mail): void
	{
		/** @var callable(string): array<string> $getHeaders */
		$getHeaders = static fn (string $name) => (array) $mail->getHeader($name);

		// Set original To, Cc, Bcc
		$counter = 0;
		foreach ($getHeaders('To') as $email => $name) {
			$mail->setHeader('X-Original-To-' . $counter++, sprintf('<%s> %s', $email, $name));
		}

		$counter = 0;
		foreach ($getHeaders('Cc') as $email => $name) {
			$mail->setHeader('X-Original-Cc-' . $counter++, sprintf('<%s> %s', $email, $name));
		}

		$counter = 0;
		foreach ($getHeaders('Bcc') as $email => $name) {
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
