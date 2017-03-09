<?php

namespace Contributte\Mail\Message;

use Nette\Mail\Message;

interface IMessageFactory
{

	/**
	 * @return Message
	 */
	public function create();

}
