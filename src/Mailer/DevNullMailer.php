<?php

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class DevNullMailer implements IMailer
{

	/**
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		// Do nothing
	}

}
