<?php declare(strict_types = 1);

namespace Contributte\Mail\Message;

use Nette\Mail\Message as NMessage;

class Message extends NMessage
{

	/**
	 * @param string[] $receivers
	 */
	public function addTos(array $receivers): void
	{
		foreach ($receivers as $email => $name) {
			$this->addTo($email, $name);
		}
	}

}
