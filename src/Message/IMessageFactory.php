<?php declare(strict_types = 1);

namespace Contributte\Mail\Message;

use Nette\Mail\Message;

interface IMessageFactory
{

	public function create(): Message;

}
