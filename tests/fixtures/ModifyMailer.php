<?php

namespace Fixtures;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

class ModifyMailer implements IMailer
{

	/**
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		$mail->setSubject('modified');
	}

}
