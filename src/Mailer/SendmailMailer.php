<?php

namespace Contributte\Mail\Mailer;

use Nette\InvalidArgumentException;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer as NSendmailMailer;
use Nette\Utils\Validators;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class SendmailMailer extends NSendmailMailer
{

	/** @var array */
	public $onSend = [];

	/** @var string */
	private $bounceMail;

	/**
	 * @param string $bounceMail
	 * @return void
	 */
	public function setBounceMail($bounceMail)
	{
		if (!Validators::isEmail($bounceMail)) {
			throw new InvalidArgumentException(sprintf('Bounce mail %s has wrong format', $bounceMail));
		}
		$this->bounceMail = $bounceMail;
	}

	/**
	 * Sends email
	 *
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		if ($this->bounceMail) {
			// Append this commands cause bounce email
			$this->commandArgs = sprintf('-f%s', $this->bounceMail);
		}

		// Trigger event
		$this->onSend($this, $mail);

		// Delegate to original mailer
		parent::send($mail);
	}

}
