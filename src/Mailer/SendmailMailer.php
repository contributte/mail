<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Contributte\Mail\Exception\Logic\InvalidArgumentException;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer as NSendmailMailer;
use Nette\Utils\Validators;

class SendmailMailer extends NSendmailMailer
{

	/** @var callable[] function (SendmailMailer $mailer, Message $mail); */
	public array $onSend = [];

	private ?string $bounceMail = null;

	public function setBounceMail(string $bounceMail): void
	{
		if (!Validators::isEmail($bounceMail)) {
			throw new InvalidArgumentException(sprintf('Bounce mail %s has wrong format', $bounceMail));
		}

		$this->bounceMail = $bounceMail;
	}

	public function send(Message $mail): void
	{
		if ($this->bounceMail !== null) {
			// Append this commands cause bounce email
			$this->commandArgs = sprintf('-f%s', $this->bounceMail);
		}

		// Trigger event
		$this->onSend($this, $mail);

		// Delegate to original mailer
		parent::send($mail);
	}

}
