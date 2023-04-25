<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

class ModifyMailer implements IMailer
{

	public function send(Message $mail): void
	{
		$mail->setSubject('modified');
	}

}
