<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;

class DevNullMailer implements Mailer
{

	public function send(Message $mail): void
	{
		// Do nothing
	}

}
