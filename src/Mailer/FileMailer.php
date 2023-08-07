<?php declare(strict_types = 1);

namespace Contributte\Mail\Mailer;

use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Utils\FileSystem;

class FileMailer implements Mailer
{

	public function __construct(private string $path)
	{
		FileSystem::createDir($this->path);
	}

	public function send(Message $mail): void
	{
		file_put_contents($this->path . '/' . date('Y-m-d H-i-s') . microtime() . '.eml', $mail->generateMessage());
	}

}
