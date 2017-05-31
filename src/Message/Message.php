<?php

namespace Contributte\Mail\Message;

use Nette\Mail\Message as NMessage;

class Message extends NMessage
{

	/**
	 * @param array $receivers
	 * @return void
	 */
	public function addTos(array $receivers)
	{
		foreach ($receivers as $email => $name) {
			$this->addTo($email, $name);
		}
	}

}
