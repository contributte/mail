<?php

namespace Contributte\Mail\Message;

use Nette\Mail\Message;

class MessageFactory implements IMessageFactory
{

	/**
	 * @return Message
	 */
	public function create()
	{
		return new Message();
	}

}
