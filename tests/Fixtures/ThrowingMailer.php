<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Exception;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

class ThrowingMailer implements Mailer
{

	public function send(Message $mail): void
	{
		throw new Exception('Test exception');
	}

}
