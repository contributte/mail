<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class DevNullMailer implements IMailer
{

	public function send(Message $mail): void
	{
		// Do nothing
	}

}
