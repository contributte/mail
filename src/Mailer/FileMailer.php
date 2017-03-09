<?php

namespace Contributte\Mail\Mailer;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class FileMailer implements IMailer
{

	/** @var string */
	private $path;

	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		@mkdir($path, 0777, TRUE);
		$this->path = realpath($path) . DIRECTORY_SEPARATOR;
	}

	/**
	 * @param Message $mail
	 * @return void
	 */
	public function send(Message $mail)
	{
		file_put_contents($this->path . date('Y-m-d H-i-s') . microtime() . '.eml', $mail->generateMessage());
	}

}
