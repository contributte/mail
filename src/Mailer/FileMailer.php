<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Contributte\Mail\Exception\RuntimeException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class FileMailer implements IMailer
{

	/** @var string */
	private $path;

	public function __construct(string $path)
	{
		if (!is_dir($path) && !mkdir($path, 0777, true)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
		}

		$this->path = realpath($path) . DIRECTORY_SEPARATOR;
	}

	public function send(Message $mail): void
	{
		file_put_contents($this->path . date('Y-m-d H-i-s') . microtime() . '.eml', $mail->generateMessage());
	}

}
