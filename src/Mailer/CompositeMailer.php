<?php

namespace Contributte\Mail\Mailer;

use Exception;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class CompositeMailer implements IMailer
{

	/** @var IMailer[] */
	private $mailers = [];

	/** @var bool */
	private $silent;

	/**
	 * @param bool $silent
	 */
	public function __construct($silent)
	{
		$this->silent = $silent;
	}

	/**
	 * @param IMailer $mailer
	 * @return void
	 */
	public function add(IMailer $mailer)
	{
		$this->mailers[] = $mailer;
	}

	/**
	 * @param Message $mail
	 * @return void
	 * @throw Exception
	 */
	public function send(Message $mail)
	{
		foreach ($this->mailers as $mailer) {
			try {
				$mailer->send(clone $mail);
			} catch (Exception $e) {
				if (!$this->silent) {
					throw $e;
				}
			}
		}
	}

}
