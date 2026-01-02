<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\Mail\Mailer\SendmailMailer;
use Nette\Mail\Message;

/**
 * Test mailer that skips parent::send() to avoid actual email sending
 */
class TestSendmailMailer extends SendmailMailer
{

	public function send(Message $mail): void
	{
		// Trigger events without sending
		foreach ($this->onSend as $callback) {
			$callback($this, $mail);
		}
	}

}
