<?php declare(strict_types = 1);

namespace Contributte\Mail\Message;

use Nette\Mail\Message as NMessage;

class Message extends NMessage
{

	/**
	 * @param array<string, string|null> $receivers
	 */
	public function addTos(array $receivers): void
	{
		foreach ($receivers as $email => $name) {
			$this->addTo($email, $name);
		}
	}

}
